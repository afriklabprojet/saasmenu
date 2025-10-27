


<div id="whatsappmessagesettings" class="hidechild">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 box-shadow">
                <div class="card-header bg-transparent py-3 d-flex align-items-center text-dark">
                    <i class="fa-brands fa-whatsapp fs-5"></i>
                    <h5 class="px-2">{{ trans('labels.whatsapp_message_settings') }}</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ URL::to('admin/settings/whatsapp_update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label fw-bold">{{ trans('labels.order_variable') }}
                                    </label>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item px-0">Order No :
                                                    <code>{order_no}</code>
                                                </li>
                                                <li class="list-group-item px-0">Payment type :
                                                    <code>{payment_type}</code>
                                                </li>
                                                <li class="list-group-item px-0">Subtotal :
                                                    <code>{sub_total}</code>
                                                </li>
                                                <li class="list-group-item px-0">Total Tax :
                                                    <code>{total_tax}</code>
                                                </li>
                                                <li class="list-group-item px-0">Delivery
                                                    charge : <code>{delivery_charge}</code></li>
                                                <li class="list-group-item px-0">Discount
                                                    amount : <code>{discount_amount}</code></li>
                                                <li class="list-group-item px-0">Grand total :
                                                    <code>{grand_total}</code>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item px-0">Customer name
                                                    : <code>{customer_name}</code></li>
                                                <li class="list-group-item px-0">Customer
                                                    mobile : <code>{customer_mobile}</code></li>
                                                <li class="list-group-item px-0">Address :
                                                    <code>{address}</code>
                                                </li>
                                                <li class="list-group-item px-0">Building :
                                                    <code>{building}</code>
                                                </li>
                                                <li class="list-group-item px-0">Landmark :
                                                    <code>{landmark}</code>
                                                </li>
                                                <li class="list-group-item px-0">Postal code :
                                                    <code>{postal_code}</code>
                                                </li>
                                                <li class="list-group-item px-0">Delivery type
                                                    : <code>{delivery_type}</code></li>
                                            </ul>
                                        </div>
                                        <div class="col-md-4">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item px-0">Notes :
                                                    <code>{notes}</code>
                                                </li>
                                                <li class="list-group-item px-0">Item Variable
                                                    : <code>{item_variable}</code></li>
                                                <li class="list-group-item px-0">Time :
                                                    <code>{time}</code>
                                                </li>
                                                <li class="list-group-item px-0">Date :
                                                    <code>{date}</code>
                                                </li>
                                                <li class="list-group-item px-0">Store name :
                                                    <code>{store_name}</code>
                                                </li>
                                                <li class="list-group-item px-0">Store URL :
                                                    <code>{store_url}</code>
                                                </li>
                                                <li class="list-group-item px-0">Track order
                                                    URL : <code>{track_order_url}</code></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label fw-bold">{{ trans('labels.item_variable') }}
                                    </label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul class="list-group list-group-flush">
                                                <li class="list-group-item px-0">Item name :
                                                    <code>{item_name}</code>
                                                </li>
                                                <li class="list-group-item px-0">QTY :
                                                    <code>{qty}</code>
                                                </li>
                                                <li class="list-group-item px-0">Variants :
                                                    <code>{variantsdata}</code>
                                                </li>
                                                <li class="list-group-item px-0">Item price :
                                                    <code>{item_price}</code>
                                                </li>
                                                <li class="list-group-item px-0">
                                                    <input type="text" name="item_message" class="form-control" placeholder="{{ trans('labels.item_message') }}" value="{{ @$settingdata->item_message }}">
                                                    @error('item_message')
                                                    <span class="text-danger" id="timezone_error">{{ $message }}</span>
                                                    @enderror
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="form-label fw-bold">{{ trans('labels.whatsapp_message') }}
                                        <span class="text-danger"> * </span> </label>
                                    <textarea class="form-control" required="required" name="whatsapp_message" cols="50" rows="10">{{ @$settingdata->whatsapp_message }}</textarea>
                                    @error('whatsapp_message')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="form-label">{{ trans('labels.contact') }}<span class="text-danger"> * </span></label>
                                    <input type="text" class="form-control" name="contact" value="{{ @$settingdata->contact }}" placeholder="{{ trans('labels.contact') }}" required>
                                    @error('contact')
                                    <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label" for="">{{ trans('labels.whatsapp_message') }}
                                </label>
                                <div class="text-center">
                                    <input id="whatsapp_on_off" type="checkbox" class="checkbox-switch"
                                        name="whatsapp_on_off" value="1"
                                        {{ $settingdata->whatsapp_on_off == 1 ? 'checked' : '' }}>
                                    <label for="whatsapp_on_off" class="switch">
                                        <span
                                            class="{{ session()->get('direction') == 2 ? 'switch__circle-rtl' : 'switch__circle' }}"><span
                                                class="switch__circle-inner"></span></span>
                                        <span
                                            class="switch__left {{ session()->get('direction') == 2 ? 'pe-2' : 'ps-2' }}">{{ trans('labels.off') }}</span>
                                        <span
                                            class="switch__right {{ session()->get('direction') == 2 ? 'ps-2' : 'pe-2' }}">{{ trans('labels.on') }}</span>
                                    </label>
                                </div>

                            </div>
                            <div class="col-md-3 form-group">
                                <label class="form-label" for="">{{ trans('labels.whatsapp_chat') }}
                                </label>
                                <div class="text-center">
                                    <input id="whatsapp_chat_on_off" type="checkbox" class="checkbox-switch"
                                        name="whatsapp_chat_on_off" value="1"
                                        {{ $settingdata->whatsapp_chat_on_off == 1 ? 'checked' : '' }}>
                                    <label for="whatsapp_chat_on_off" class="switch">
                                        <span
                                            class="{{ session()->get('direction') == 2 ? 'switch__circle-rtl' : 'switch__circle' }}"><span
                                                class="switch__circle-inner"></span></span>
                                        <span
                                            class="switch__left {{ session()->get('direction') == 2 ? 'pe-2' : 'ps-2' }}">{{ trans('labels.off') }}</span>
                                        <span
                                            class="switch__right {{ session()->get('direction') == 2 ? 'ps-2' : 'pe-2' }}">{{ trans('labels.on') }}</span>
                                    </label>
                                </div>

                            </div>
                            <div class="col-md-3 form-group">
                                <p class="form-label">
                                    {{ trans('labels.whatsapp_chat_position') }}
                                </p>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input form-check-input-secondary"
                                        type="radio" name="whatsapp_chat_position"
                                        id="chatradio" value="1"
                                        {{ @$settingdata->whatsapp_chat_position == '1' ? 'checked' : '' }} />
                                    <label for="chatradio"
                                        class="form-check-label">{{ trans('labels.left') }}</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input form-check-input-secondary"
                                        type="radio" name="whatsapp_chat_position"
                                        id="chatradio1" value="2"
                                        {{ @$settingdata->whatsapp_chat_position == '2' ? 'checked' : '' }} />
                                    <label for="chatradio1"
                                        class="form-check-label">{{ trans('labels.right') }}</label>
                                </div>
                            </div>
                            <div class="form-group text-end">
                                <button class="btn btn-success btn-succes mt-3 {{ Auth::user()->type == 4 ? (helper::check_access('role_settings', Auth::user()->role_id, $vendor_id, 'add') == 1 || helper::check_access('role_settings', Auth::user()->role_id, $vendor_id, 'edit') == 1 ? '' : 'd-none') : '' }}"  @if (env('Environment')=='sendbox' ) type="button" onclick="myFunction()" @else type="submit" @endif>{{ trans('labels.save') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>