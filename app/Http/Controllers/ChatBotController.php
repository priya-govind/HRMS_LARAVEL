<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatbotSession;
use App\Models\ChatbotMessage;
use App\Models\ChatbotIntent;
use App\Models\ChatbotResponse;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\BotMenu;
use App\Services\AttendanceService;
use App\Services\ReportService;
use Carbon\Carbon;
use Auth;

class ChatBotController extends Controller
{
   public function handleCommand(string $command, Request $request){
    $menu = BotMenu::where('command', $command)->first();

    if (!$menu) {
        return ['reply' => 'Unknown command'];
    }

    //  If submenu exists → return children
    $roleId   = session('role_id');
    $children = BotMenu::where('parent_id', $menu->id)
        ->whereIn('id', function($query) use ($roleId) {
            $query->select('bot_id')
                  ->from('role_bot_menus_permissions')
                  ->where('roles_id', $roleId);
        })
        ->get();

    if ($children->isNotEmpty()) {
        return [
            'reply'    => 'Choose an option:',
            'submenus' => $children->map(fn($child) => [
                'label'   => $child->bot_name ?? $child->name,
                'command' => $child->command,
            ])->toArray()
        ];
    }

    //  Check required fields
    $fields = $menu->required_fields ?? [];
    $missing = [];
    $args = [];

    foreach ($fields as $field) {
        $name = $field['name'];
        if (!$request->has($name)) {
            $missing[] = $field['label'];
        } else {
            $args[] = $request->input($name);
        }
    }

    if (!empty($missing)) {
        return [
            'reply' => implode("\n", $missing),
            'required_fields' => $fields,
            'command' => $command
        ];
    }

    //  Call service dynamically
    $serviceClass = "\\App\\Services\\" . $menu->service_name;
    if (!class_exists($serviceClass)) {
        return ['reply' => "Service {$menu->service_name} not found."];
    }

    $service = new $serviceClass();
    $method = $menu->service_method;

    if (!method_exists($service, $method)) {
        return ['reply' => "Method {$method} not implemented in {$menu->service_name}."];
    }

    return $service->{$method}(Auth::id(), ...$args);
    }

    // ------------------ Handlers ------------------
    protected function handleAttendanceInfo(Request $request) {
        $roleId   = session('role_id');
        $parentId = $request->input('parent_id');

        $submenus = BotMenu::where('parent_id', $parentId)
            ->whereIn('id', function($query) use ($roleId) {
                $query->select('bot_id')
                    ->from('role_bot_menus_permissions')
                    ->where('roles_id', $roleId);
            })
            ->orderBy('order_by', 'ASC')
            ->get()
            ->map(function($menu) {
                return [
                    'label'   => $menu->bot_name ?? $menu->name,
                    'command' => $menu->command,
                ];
            })
            ->toArray();

        return [
            'reply'    => 'Choose your attendance option:',
            'submenus' => $submenus,
        ];
    }
    protected function handleAttendanceReport()
    {
        $reportService = new ReportService();
        $report = $reportService->getAttendanceReport(Auth::id());
        return ['reply' => 'Here is your attendance report:', 'data' => $report];
    }

    protected function handleTimesheetReport()
    {
        $reportService = new ReportService();
        $report = $reportService->getTimesheetReport(Auth::id());
        return ['reply' => 'Here is your timesheet report:', 'data' => $report];
    }

    protected function handleLeaveReport()
    {
        $reportService = new ReportService();
        $report = $reportService->getLeaveReport(Auth::id());
        return ['reply' => 'Here is your leave report:', 'data' => $report];
    }

        //  Helper to format attendance rows
    protected function formatAttendanceResponse($rows, string $title){
    if (!$rows || empty($rows)) {
        return ['reply' => 'No attendance record found.'];
    }

    $data = [];
    foreach ((is_iterable($rows) ? $rows : [$rows]) as $row) {
        $data[] = [
                'Name'        => $row->user->name ?? 'Unknown',
                'Date'        => Carbon::createFromFormat('d/m/Y', $row->chkinDate)->format('d M,Y'),
                'Check In'    => Carbon::createFromFormat('d/m/Y H:i:s', $row->chkinDate)->format('H:i A'),
                'Check Out'   => $row->chkoutDate 
                                    ? Carbon::createFromFormat('d/m/Y H:i:s', $row->chkoutDate)->format('H:i A') 
                                    : 'Still Checked In',
                'Worked Hours'=> $row->work_duration ?? 'still working',
            ];
    }

    return ['reply' => $title, 'data' => $data];
}

    public function sendMessage(Request $request)
{
    $roleId = session('role_id');
    $cacheKey = "bot_menus_role_{$roleId}";

    $menuData = Cache::remember($cacheKey, 600, function() use ($roleId) {
        return BotMenu::where('parent_id', 1)
            ->whereIn('id', function($query) use ($roleId) {
                $query->select('bot_id')
                      ->from('role_bot_menus_permissions')
                      ->where('roles_id', $roleId);
            })
            ->orderBy('order_by', 'ASC')
            ->get()
            ->map(function($menu) {
                return [
                    'name'      => $menu->bot_name ?? $menu->name,
                    'parent_id' => $menu->id,
                    'command'   => $menu->command,
                ];
            })->toArray();
    });

    $user = Auth::user();
    $session = ChatbotSession::firstOrCreate([
        'user_id' => $user->id,
        'status'  => 'active'
    ]);

    // 🔹 Log user message
    ChatbotMessage::create([
        'chatbot_session_id' => $session->id,
        'sender'             => 'user',
        'content'            => $request->text ?? $request->content,
        'request_payload'    => json_encode($request->all()) // NEW
    ]);

    $text = strtolower(trim($request->text));

    // 🔹 Greeting case
    if (in_array($text, ['hi bot','hello bot','hi','hello'])) {
        $reply = "Hi {$user->name}, how may I help you today? Here are your available menus:";
        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'sender'             => 'bot',
            'content'            => $reply,
            'response_payload'   => json_encode(['reply'=>$reply,'menus'=>$menuData]) // NEW
        ]);
        return response()->json([
            'reply' => $reply,
            'menus' => $menuData
        ]);
    }

    // 🔹 Command routing via DB
    $menu = BotMenu::where('command', $text)->first();
    if ($menu) {
        $response = $this->handleCommand($text, $request);
        ChatbotMessage::create([
            'chatbot_session_id' => $session->id,
            'sender'             => 'bot',
            'content'            => $response['reply'] ?? '',
            'response_payload'   => json_encode($response) // NEW
        ]);
        return response()->json($response);
    }

    // 🔹 Intent detection fallback
    $intent = ChatbotIntent::whereJsonContains('patterns', $text)->first();
    if ($intent) {
        $response = ChatbotResponse::where('chatbot_intent_id', $intent->id)->first();
        $reply = $response ? $response->template : "I understood your intent: {$intent->name}";
    } else {
        $reply = "Sorry, I didn't understand. Try asking about Attendance, Leave, or Timesheet.";
    }

    ChatbotMessage::create([
        'chatbot_session_id' => $session->id,
        'sender'             => 'bot',
        'content'            => $reply,
        'response_payload'   => json_encode(['reply'=>$reply]) // NEW
    ]);

    if ($intent && in_array($intent->name, ['menu','help'])) {
        return response()->json([
            'reply' => $reply,
            'menus' => $menuData
        ]);
    }

    return response()->json([
        'reply' => $reply
    ]);
}
public function history()
{
    $session = ChatbotSession::where('user_id', Auth::id())
        ->where('status','active')
        ->first();

    if (!$session) return [];

    return ChatbotMessage::where('chatbot_session_id', $session->id)
        ->orderBy('created_at')
        ->get();
}
}