@extends('layouts.default')

@section('content')
    <h2 class="header">Checkout</h2>

    <ul class="tabs tabs-fixed-width">
        <li class="tab"><a href="#step1">Pagamento</a></li>
        <li class="tab"><a href="#step2">Suas informações</a></li>
    </ul>

    <form action="/checkout/{{ $id }}" method="post" id="form">

        {{ csrf_field() }}

        <input type="hidden" name="itemId1" value="0001">
        <input type="hidden" name="itemDescription1" value="Produto PagSeguroI">
        <input type="hidden" name="itemAmount1" value="5.00">
        <input type="hidden" name="itemQuantity1" value="1">
        <input type="hidden" name="senderHash" id="senderHash">
        <input type="hidden" name="shippingCost" value="0.00">
        <input type="hidden" name="creditCardToken" id="creditCardToken">
        <input type="hidden" name="installmentValue" id="installmentValue">

        <div id="step1">
            <p>Preencha os dados para pagamento</p>

            <div class="row">
                <div class="input-field col s12">
                    <input type="text" id="creditCardHolderName" name="creditCardHolderName">
                    <label for="creditCardHolderName">Nome completo</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <input type="text" id="creditCardHolderCPF" name="creditCardHolderCPF">
                    <label for="creditCardHolderCPF">CPF</label>
                </div>
                <div class="input-field col s6">
                    <input type="text" id="creditCardHolderPhone" name="creditCardHolderPhone">
                    <label for="creditCardHolderPhone">Telefone</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s3">
                    <input type="text" id="cardNumber" value="5359889213778873">
                    <label for="cardNumber">Número do cartão</label>
                    <div id="card_brand"></div>
                </div>
                <div class="input-field col s3">
                    <input type="text" id="cvv" value="901">
                    <label for="cvv">Código de segurança</label>
                </div>
                <div class="input-field col s3">
                    <input type="text" id="expirationMonth" value="11">
                    <label for="expirationMonth">Mês de expiração</label>
                </div>
                <div class="input-field col s3">
                    <input type="text" id="expirationYear" value="2020">
                    <label for="expirationYear">Ano de expiração</label>
                </div>                
            </div>
            <div class="row">
                <div class="col s4">
                    <select name="installmentQuantity" id="installmentQuantity" class="browser-default">
                        <option disabled selected>Parcelamento</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                    <input type="submit" value="pagar" class="btn">
                </div>
            </div>
        </div>
        
        <div id="step2">
            <p>Preencha suas informações</p>
            <div class="row">
                <div class="input-field col s12">
                    <input type="text" id="senderName" name="senderName" value="Anderson Santa Rosa da Silva">
                    <label for="senderName">Nome completo</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <input type="text" id="senderCPF" name="senderCPF" value="72678356191">
                    <label for="senderCPF">CPF</label>
                </div>
                <div class="input-field col s6">
                    <input type="text" id="senderEmail" name="senderEmail" value="c18831602765593831825@sandbox.pagseguro.com.br">
                    <label for="senderEmail">Email</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    <input type="text" id="senderPhone" name="senderPhone" value="61986412007">
                    <label for="senderPhone">Telefone</label>
                </div>
            </div>
        </div>
    </form>

    {{-- <div id="payment_methods" class="center-align"></div> --}}
@endsection

@section('script')

<script type="text/javascript" src="https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"></script>
<script src="/js/pagseguro.js"></script>
<script>
    const paymentData = {
        brand: '',
        amount: {{ $amount }},
    }

    PagSeguroDirectPayment.setSessionId('{!! $session !!}');

    pagSeguro.getPaymentMethods(paymentData.amount)
        .then(function (urls) {
            let html = '';

            urls.forEach(function (url) {
                html += '<img src="' + url + '" class="credit_card">'
            });

            $('#payment_methods').html(html);
        });
    
    $('#senderName').on('change', function () {
        pagSeguro.getSenderHash().then(function(data) {
            $('#senderHash').val(data);
        })
    });

    $('#shippingAddressPostalCode').on('keyup', function () {
        let cep = $(this).val();

        if (cep.length == 8) {
            $.get('https://viacep.com.br/ws/' + cep + '/json/')
                .then(function (res) {
                    $('#shippingAddressDistrict').val(res.bairro);
                    $('#shippingAddressCity').val(res.localidade);
                    $('#shippingAddressStreet').val(res.logradouro);
                    $('#shippingAddressState').val(res.uf);
                    Materialize.updateTextFields();
                })
        }
    });

    $('#cardNumber').on('keyup', function() {
        if ($(this).val().length >= 6) {
            let bin = $(this).val();
            pagSeguro.getBrand(bin)
                .then(function (res) {
                    paymentData.brand = res.result.brand.name;
                    $('#card_brand').html('<img src="' + res.url + '" class="credit_card">')
                    return pagSeguro.getInstallments(paymentData.amount, paymentData.brand);
                })
                .then(function(res) {
                    let html = '';
                    res.forEach(function (item) {
                        html += '<option value="' + item.quantity + '">' + item.quantity + 'x R$' + item.installmentAmount + ' - total R$' + item.totalAmount + '</option>'
                    });
                    $('#installmentQuantity').html(html);
                    $('#installmentValue').val(res[0].installmentAmount);
                    $('#installmentQuantity').on('change', function () {
                        let value = res[$('#installmentQuantity').val() - 1].installmentAmount;
                        $('#installmentValue').val(value);
                    });
                })
        }
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        let params = {
            cardNumber: $('#cardNumber').val(),
            cvv: $('#cvv').val(),
            cardNumber: $('#cardNumber').val(),
            expirationMonth: $('#expirationMonth').val(),
            expirationYear: $('#expirationYear').val(),
            brand: paymentData.brand
        }
        pagSeguro.createCardToken(params).then(function (token) {
            $('#creditCardToken').val(token);

            let url = $('#form').attr('action');
            let data = $('#form').serialize();

            console.log(data);

            $.post(url, data).then(function (result) {
                console.log(result);
            });
        });
    });

</script>
@endsection