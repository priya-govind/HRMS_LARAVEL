{{-- Sidebar Category Component --}}
@props(['category'])

@if ($category->support_access == '1' && session('support_access') != 1)
    {{-- Do not render parent if restricted --}}
@else
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="collapse" href="#{{ $category->url_link }}" aria-expanded="false" aria-controls="{{ $category->url_link }}" title="{{ $category->category_name }}">
            <span class="menu-title">{{ $category->category_name }}</span>
            <i class="mdi mdi-menu-down"></i>
        </a>
        <div class="collapse" id="{{ $category->url_link }}">
            <ul class="nav flex-column sub-menu">
                @foreach ($category->children as $subCategory)
                    @if ($subCategory->support_access == '1' && session('support_access') != 1)
                        {{-- Skip restricted child --}}
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="/{{ $subCategory->url_link }}" title="{{ $subCategory->category_name }}">
                                {{ $subCategory->category_name }}
                            </a>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
    </li>
@endif