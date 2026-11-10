@foreach($paymentGateways as $paymentGateway)
<tr>
    <td>{{$loop->iteration}}</td>
    <td><img src="{{asset('uploads/'.$paymentGateway->image)}}" alt="{{$paymentGateway->name}}"></td>
    <td>{{$paymentGateway->name}}</td>
    <td>{{$paymentGateway->url}}</td>
    <td>{{$paymentGateway->api_key}}</td>
    <td>{{$paymentGateway->collection_id}}</td>
    <td>
        <label class="switch-btn-wrapper">
        <span class="custom-switch-btn">
            <input type="checkbox" class="toggle-class" name="status" id="status" value="1" data-id="{{$paymentGateway->id}}" data-text="PaymentGateway" @if(optional($paymentGateway)->status=='1') checked @endif >
            <span class="slider round"></span>
        </span>
        </label>
    </td>
    <td>
        <div class="dropdown table-action">
            <button type="button" data-bs-toggle="dropdown">
                <i class="far fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu">
            <a href="#payment-settings" class="btn action-btn bg-light-orange edit-item" data-bs-toggle="modal" id="payment-gateway-{{$paymentGateway->id}}" data-id="{{$paymentGateway->id}}" data-image="{{$paymentGateway->image}}" data-name="{{$paymentGateway->name}}" data-url="{{$paymentGateway->url}}" data-api-key="{{$paymentGateway->api_key}}"
               data-collection-id="{{$paymentGateway->collection_id}}"><i class="far fa-edit"></i>@lang('Edit')
            </a>
            <a href="{{ route('admin.payment-gateway.destroy', $paymentGateway->id) }}" data-bs-toggle="modal" class="btn action-btn bg-light-red delete-item" data-bs-target="#delete-modal" id="delete-item-{{$paymentGateway->id}}" data-id="{{$paymentGateway->id}}"><i class="fas fa-trash"></i>@lang('Delete')</a>
            </ul>
        </div>
    </td>
</tr>
@endforeach
