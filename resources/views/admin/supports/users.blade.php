@foreach ($landlords as $landlord)
    @php
     $latestMessage = $landlord->latestMessage;
    @endphp
    <div class="chat-profile user-chat-list"
        data-route="{{ route('admin.supports.getDetailsMsg', $landlord->id) }}"
        data-user-name="{{ $landlord->name }}"
        data-user-img="{{ asset($landlord->image ?? 'backend/assets/img/icon/chat-profile.svg') }}"
        data-user-id="{{ $landlord->id }}">

        <div class="d-flex align-items-center gap-2">
            <img src="{{ asset($landlord->image ?? 'backend/assets/img/icon/chat-profile.svg') }}">
            <div>
                <p class="user-name">{{ $landlord->name }}</p>
                @if(!empty($latestMessage->message))
                    <p class="user-send-img">{{ Str::limit($latestMessage->message ?? '', 30) }}</p>
                @elseif(!empty($latestMessage->file))
                    <p class="user-send-img text-muted">{{ __('Sent a file') }}</p>
                @else
                <p class="user-send-img text-muted">{{ __('No message yet') }}</p>
                @endif
            </div>
        </div>

        <div class="time-and-count">
            @if($latestMessage)
                <p>{{ \Carbon\Carbon::parse($latestMessage->created_at)->format('g:i A') }}</p>
            @endif
            @if($landlord->unread_count > 0)
                <span>{{ $landlord->unread_count }}</span>
            @endif
        </div>
    </div>
@endforeach
