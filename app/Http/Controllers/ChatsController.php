<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class ChatsController extends Controller
{
    public function index(Request $request)
    {
        $loadChat=true;
         $userName = $request->get('user_name');
            $userId = null;

            if ($userName) {
                $user = User::where('name', $userName)->first();
                if ($user) {
                    $userId = $user->id;
                    Chat::where('receiver_id',session('user_id'))->where('sender_id',$userId)->update([
                        'is_read' => config('global.notify_read')
                    ]);
                } 
                
            }

        return view('chats/chats',compact('loadChat','userId', 'userName'));
    }
  public function search(Request $request)
    {
        $query = $request->get('q');
        $currentUserId = Auth::id();
        $today = now()->toDateString();
        $limit=($request->path=='dashboard')? 5 : 10;
        $users = User::where('id', '!=', $currentUserId)
                    ->where('id','!=',config('global.superadmin_id'))
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->limit($limit)
                    ->get()
                    ->map(function ($user) use ($currentUserId, $today) {
                        $isBlocked = $this->isUserBlocked($user->id);
                        $lastMessage = $this->getLastMessage($user->id, $currentUserId);
                        $unreadCount = $this->getUnreadCount($user->id, $currentUserId);
                        $lastMessageTime = $lastMessage ? $lastMessage->created_at : null;
                        $isToday = $lastMessageTime && $lastMessageTime->toDateString() === $today;
                        $isMuted = Auth::user()->mutedUsers()->where('muted_user_id', $user->id)->exists();

                        return [
                            'id'                => $user->id,
                            'name'              => $user->name,
                            'email'             => $user->email,
                            'profile_image'     => $user->image,
                            'unread_count'      => $unreadCount,
                            'last_message_time' => $lastMessageTime ? $lastMessageTime->format('d M,Y H:i a') : null,
                            'last_message'      => $lastMessage ? $lastMessage->message : null,
                            'isBlocked'         => $isBlocked,
                            'is_today'          => $isToday,
                            'isMuted'           => $isMuted,
                            'attachment_path'   => $lastMessage->attachment_path ?? null,   
                        ];
                    });
        $sortedUsers = $users->sortByDesc(fn ($user) => $user['is_today'] ? 1 : 0)
                             ->sortByDesc(fn ($user) => $user['last_message_time'] ?? '0000-00-00 00:00:00');
        return response()->json($sortedUsers->values()->all());
    }
    public function quick_search(Request $request)
    {
        $query = $request->get('q');
        $currentUserId = Auth::id();
        $today = now()->toDateString();
        $limit=($request->path=='dashboard')? 5 : 10;
        $users = User::where('id', '!=', $currentUserId)
                    ->where('id','!=',config('global.superadmin_id'))
                    ->where(function ($q) use ($query) {
                        $q->where('name', 'like', "%{$query}%")
                        ->orWhere('email', 'like', "%{$query}%");
                    })
                    ->limit($limit)
                    ->get()
                    ->map(function ($user) use ($currentUserId, $today) {
                        $isBlocked = $this->isUserBlocked($user->id);
                        $lastMessage = $this->getLastMessage($user->id, $currentUserId);
                        $unreadCount = $this->getUnreadCount($user->id, $currentUserId);
                        $lastMessageTime = $lastMessage ? $lastMessage->created_at : null;
                        $isToday = $lastMessageTime && $lastMessageTime->toDateString() === $today;
                        $isMuted = Auth::user()->mutedUsers()->where('muted_user_id', $user->id)->exists();

                        return [
                            'id'                => $user->id,
                            'name'              => $user->name,
                            'email'             => $user->email,
                            'profile_image'     => $user->image,
                            'unread_count'      => $unreadCount,
                            'last_message_time' => $lastMessageTime ? $lastMessageTime->format('d M,Y H:i a') : null,
                            'last_message'      => $lastMessage ? $lastMessage->message : null,
                            'isBlocked'         => $isBlocked,
                            'is_today'          => $isToday,
                            'isMuted'           => $isMuted,
                            'attachment_path'   => $lastMessage->attachment_path ?? null,   
                        ];
                    });
        $sortedUsers = $users->sortByDesc(fn ($user) => $user['is_today'] ? 1 : 0)
                             ->sortByDesc(fn ($user) => $user['last_message_time'] ?? '0000-00-00 00:00:00');
          $totalUnread = Chat::where('receiver_id', session('user_id'))
              ->where('is_read', config('global.notify_unread'))
              ->count();
              $chat_info=$sortedUsers->values()->all();
              $total_cnt=$totalUnread;
        return response()->json(['chat_info'=> $chat_info,'total_cnt' => $total_cnt]);
    }

    private function isUserBlocked($userId)
    {
        return Auth::user()->blockedUsers()->where('blocked_user_id', $userId)->exists();
    }

    private function getLastMessage($userId, $currentUserId)
    {
        return Chat::where(function ($query) use ($userId, $currentUserId) {
            $query->where('sender_id', $userId)->where('receiver_id', $currentUserId);
        })->orWhere(function ($query) use ($userId, $currentUserId) {
            $query->where('sender_id', $currentUserId)->where('receiver_id', $userId);
        })->latest()->first();
    }

    private function getUnreadCount($userId, $currentUserId)
    {
        return Chat::where('sender_id', $userId)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', config('global.notify_unread'))
            ->count();
    }

    public function getMessages($userId)
    {
    try {
        $currentUser = Auth::user();
        $isBlocked = $this->isUserBlocked($userId);
        $isMuted = $currentUser->mutedUsers()->where('muted_user_id', $userId)->exists();

        if ($isBlocked) {
            return response()->json(['isBlocked' => true, 'isMuted' => $isMuted, 'messages' => []]);
        }

        Chat::where('sender_id', $userId)
            ->where('receiver_id', $currentUser->id)
            ->where('is_read', config('global.notify_unread'))
            ->update(['is_read' => config('global.notify_read')]);

        $messages = Chat::where(function ($query) use ($currentUser, $userId) {
                $query->where('sender_id', $currentUser->id)->where('receiver_id', $userId);
            })
            ->orWhere(function ($query) use ($currentUser, $userId) {
                $query->where('sender_id', $userId)->where('receiver_id', $currentUser->id);
            })
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) {
                return [
                    'id'          => $msg->id,
                    'sender_id'   => $msg->sender_id,
                    'receiver_id' => $msg->receiver_id,
                    'message'     => $msg->message,
                    'created_at'  => $msg->created_at->toDateTimeString(),
                    'seen'        => $msg->is_read,
                    'reply_to' => $msg->reply_to,
                    'attachment_path' => $msg->attachment_path,
                    'reply_message' => $msg->repliedMessage?->message,
                ];
            });

        return response()->json([
            'isBlocked' => false,
            'isMuted'   => $isMuted,
            'messages'  => $messages
        ]);

    } catch (\Exception $e) {
        Log::error('Chat load error: ' . $e->getMessage());
        return response()->json(['error' => 'Server error'], 500);
    }
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message'     => 'required|string|max:5000',
             'reply_to' => 'nullable|exists:chat,id'
        ]);

        $receiverId = $request->receiver_id;
        $currentUserId = Auth::id();

        if ($this->isUserBlocked($receiverId)) {
            return response()->json(['error' => 'You have blocked this user.'], 403);
        }  

        if (User::find($receiverId)->blockedUsers()->where('blocked_user_id', $currentUserId)->exists()) {
            return response()->json(['error' => 'You are blocked by this user.'], 403);
        }
        Log::info('Reply to:', ['reply_to' => $request->reply_to]);
        $chat = Chat::create([
            'sender_id'   => $currentUserId,
            'receiver_id' => $receiverId,
            'message'     => $request->message,
            'reply_to' => $request->reply_to 
        ]);

        Notification::create([
            'sender_id'      => $currentUserId,
            'receiver_id'  => $receiverId,
            'sender_name' => session('user_name'),
            'notify_type'         => 'chat',
            'subject'      => 'New Message Received',
            'message'      => "<a href='".url('chats?user_name='.$msg->sender->name) ."'><strong>" . Auth::user()->name . "</strong> have sent a message.<br/></a>",
            //'redirect_url' => route('chats.index'),
            'is_read'      => config('global.notify_unread'),
        ]);
        $superAdmins = User::where('id', config('global.superadmin_id'))->get();
        foreach ($superAdmins as $admin) {
            Notification::create([
                'sender_id'      => $currentUserId,
                'sender_name' => session('user_id'),
                'receiver_id'  => $admin->id,
                'notify_type'         => 'chat',
                'subject'      => 'New Message Sent',
                'message'      => "User <strong>" . Auth::user()->name . "</strong> sent a message to <strong>" . User::find($receiverId)->name . "</strong>.",
                //'redirect_url' => route('chats.index'),
                'is_read'      => config('global.notify_unread'),
            ]);
        }
        return response()->json([
            'success' => true,
            'message' => [
                'id'         => $chat->id,
                'content'    => $chat->message,
                'created_at' => $chat->created_at->toDateTimeString(),
                'is_read'    => config('global.notify_unread'),
                'sender'     => 'me',
            ]
        ]);
    } 
    public function deleteMessage($id)
    {
        $userId = Auth::id();

        // Delete all messages between current user and selected user
        Chat::where(function($q) use ($id, $userId) {
            $q->where('sender_id', $userId)
            ->where('receiver_id', $id);
        })->orWhere(function($q) use ($id, $userId) {
            $q->where('sender_id', $id)
            ->where('receiver_id', $userId);
        })->delete();

        return response()->json(['success' => true]);
    }

    public function blockUser($userId)
    {
        $user = Auth::user();
        $userToBlock = User::findOrFail($userId);

        if ($user->blockedUsers()->where('blocked_user_id', $userToBlock->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User is already blocked.'], 400);
        }

        $user->blockedUsers()->attach($userToBlock->id);

        return response()->json(['success' => true, 'message' => 'User blocked successfully.']);
    }

    public function unblockUser($userId)
    {
        $user = Auth::user();
        $blockedUser = User::findOrFail($userId);

        if (!$user->blockedUsers()->where('blocked_user_id', $blockedUser->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User is not blocked.'], 400);
        }

        $user->blockedUsers()->detach($blockedUser->id);

        return response()->json(['success' => true, 'message' => 'User unblocked successfully.']);
    }

    public function muteUser($userId)
    {
        $user = Auth::user();
        $userToMute = User::findOrFail($userId);

        if ($user->mutedUsers()->where('muted_user_id', $userToMute->id)->exists()) {
            return response()->json(['success' => false, 'message' => 'User is already muted.'], 400);
        }

        $user->mutedUsers()->attach($userToMute->id);

        return response()->json(['success' => true, 'message' => 'User muted successfully.']);
    }

    public function unmuteUser($userId)
    {
        $user = Auth::user();
        $user->mutedUsers()->detach($userId);

        return response()->json(['success' => true, 'message' => 'User unmuted successfully.']);
    }
    public function clearMessages($userId)
    {
        $currentUserId = Auth::id();

        $deleted = Chat::where(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $currentUserId)->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($currentUserId, $userId) {
            $query->where('sender_id', $userId)->where('receiver_id', $currentUserId);
        })->delete();

        return response()->json([
            'success' => true,
            'message' => 'Chat messages cleared successfully.',
            'deleted_count' => $deleted,
        ]);
    }
        public function markChatNotificationsAsRead($senderId)
    {
        $userId = auth()->id();

        Notification::where('receiver_id', $userId)
            ->where('sender_id', $senderId)
            ->where('notify_type', 'chat')
            ->where('is_read', config('global.notify_unread'))
            ->update(['is_read' => config('global.notify_read')]);

        return response()->json(['success' => true]);
    }
    public function replyToMessage(Request $request)
    {
        $request->validate([
            'original_message_id' => 'required|exists:chat,id',
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string|max:5000',
        ]);

        $currentUserId = Auth::id();

        $chat = Chat::create([
            'sender_id' => $currentUserId,
            'receiver_id' => $request->receiver_id,
            'message' => $request->message,
            'reply_to' => $request->original_message_id, // Add this column to your chats table
        ]);

        return response()->json(['success' => true, 'message' => $chat]);
    }
    public function forwardMessage(Request $request)
    {
        $request->validate([
            'original_message_id' => 'required|exists:chat,id', 
            'receiver_name' => 'required|string|exists:users,name'  
        ]);

        $receiver = User::where('name', $request->receiver_name)->first();
        $original = Chat::find($request->original_message_id);
        $currentUserId = Auth::id();

        $chat = Chat::create([
            'sender_id' => $currentUserId,
            'receiver_id' => $receiver->id, // 
            'message' => '[Forwarded] ' . $original->message,
            'reply_to' => $original->id,
            'forwarded_from' => $original->sender_id, 
        ]);

        return response()->json(['success' => true, 'message' => $chat]);
    }
    public function upload(Request $request)
    {
        $request->validate([
            'attachment' => 'required|file|max:5120',
            'receiver_id' => 'required|exists:users,id'
        ]);

        $file = $request->file('attachment');
        $filename = time() . '_' . $file->getClientOriginalName();
        $destination = public_path('images/chat_attachments');

        // Ensure folder exists
        if (!file_exists($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $chat = Chat::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->receiver_id,
            'message' => '[File]',
            'attachment_path' => 'images/chat_attachments/' . $filename
        ]);

        return response()->json(['success' => true, 'chat' => $chat]);
    }



}