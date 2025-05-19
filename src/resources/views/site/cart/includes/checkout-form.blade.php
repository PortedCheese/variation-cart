<div class="card cart-info">
    <div class="card-body cart-info__header">
        <form action="{{ route("catalog.cart.order") }}" method="post" id="checkout-order-form">
            @csrf

            <div class="form-group upper-label">
                <input type="text"
                       id="name"
                       name="name"
                       required
                       value="{{ old('name', $user ? $user->full_name : "") }}"
                       class="form-control @error("name") is-invalid @enderror">
                <label for="name">Имя <span class="text-danger">*</span></label>
                @error("name")
                    <div class="invalid-feedback" role="alert">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group upper-label">
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email', $user ? $user->email : "") }}"
                       class="form-control @error("email") is-invalid @enderror">
                <label for="email">E-mail</label>
                @error("email")
                    <div class="invalid-feedback" role="alert">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group upper-label">
                <input type="text"
                       id="phone"
                       name="phone"
                       value="{{ old('phone', $user ? $user->phone_number : "") }}"
                       class="form-control @error("phone") is-invalid @enderror">
                <label for="phone">Номер телефона</label>
                @error("phone")
                    <div class="invalid-feedback" role="alert">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group upper-label">
                <textarea
                       id="comment"
                       name="comment"
                       class="form-control @error("comment") is-invalid @enderror">{{ old('comment') }}</textarea>
                <label for="comment">Сообщение</label>
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
                           name="privacy_policy">
                    <label class="custom-control-label" for="privacy_policy">
                        Я даю свое
                        @if (\Illuminate\Support\Facades\Route::has("policy"))
                            <a href="#agreementModal" data-bs-toggle="modal" data-bs-target="#agreementModal">Согласие на обработку персональных данных</a> и принимаю условия <a href="{{ route("policy") }}" target="_blank">Политики по обработке персональных данных</a>
                        @else
                           Cогласие на обработку персональных данных и принимаю условия Политики по обработке персональных данных
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