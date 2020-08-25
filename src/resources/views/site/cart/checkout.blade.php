@extends("layouts.boot")

@section('page-title', "Оформление заказа")

@section("header-title", "Оформление заказа")

@section("contents")
    <div class="row">
        <div class="col-12 col-md-8 col-lg-9 mb-3">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route("catalog.cart.order") }}" method="post" id="checkout-order-form">
                        @csrf

                        <div class="form-group">
                            <label for="name">Имя <span class="text-danger">*</span></label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   required
                                   value="{{ old('name', $user ? $user->full_name : "") }}"
                                   class="form-control @error("name") is-invalid @enderror">
                            @error("name")
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $user ? $user->email : "") }}"
                                   class="form-control @error("email") is-invalid @enderror">
                            @error("email")
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="phone">Номер телефона</label>
                            <input type="text"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}"
                                   class="form-control @error("phone") is-invalid @enderror">
                            @error("phone")
                            <div class="invalid-feedback" role="alert">
                                {{ $message }}
                            </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comment">Сообщение</label>
                            <input type="text"
                                   id="comment"
                                   name="comment"
                                   value="{{ old('comment') }}"
                                   class="form-control @error("comment") is-invalid @enderror">
                            @error("comment")
                                <div class="invalid-feedback" role="alert">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input @error("privacy_policy") is-invalid @enderror"
                                       id="privacy_policy"
                                       required
                                       checked
                                       name="privacy_policy">
                                <label class="custom-control-label" for="privacy_policy">
                                    @if (\Illuminate\Support\Facades\Route::has("policy"))
                                        Согласие с <a href="{{ route("policy") }}">"Политикой конфиденциальности"</a>
                                    @else
                                        Согласие с "Политикой конфиденциальности"
                                    @endif
                                </label>
                                @error("privacy_policy")
                                    <div class="invalid-feedback" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 col-lg-3">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Ваш заказ</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        @foreach ($cartItems as $item)
                            <li>
                                {{ $item->product->title }}
                                {{ $item->variation->description }}
                                {{ $item->quantity }} x {{ $item->variation->price }}
                            </li>
                        @endforeach
                    </ul>
                    <hr>
                    <div>Товары на сумму {{ $cart->total }}</div>
                </div>
                <div class="card-body">
                    <button type="submit" form="checkout-order-form" class="btn btn-primary btn-block">Оформить заказ</button>
                </div>
            </div>
        </div>
    </div>
@endsection