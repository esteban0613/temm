@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="areaPartner">
            <section id="registro" class="headIntro auto_margin">
                <div class="row">
                    <div class="col-lg-8 col-lg-8">
                        <div class="text-center">
                            <!--<figure><img src="images/wasi-partner.svg" class="img-fluid" alt=""></figure>-->
                            <h2>SOFTWARE PARA GESTIÓN DE <strong>PARQUEADEROS, CAFÉS Y MUCHO MAS</strong></h2>
                            <h3>Contacto: <strong>3207329971</strong></h3>
                            <p><a href="tel:+573207329971">Click para llamar</a></p>
                            <p><a href="https://wa.link/89a3ot" target="_blank">Click para WHATSAPP</a></p>
                            <img src="images/box-shadow.png" width="220" alt="">
                            <!--<p>Integer fringilla enim eget accumsan tempus. Vivamus nec odio eget sapien convallis condimentum Etiam ac eros quis purus egestas ornare
                                ut eu augue.</p>-->
                        </div>
                    </div>
                    <div class="col-lg-4 col-lg-4">
                        <div class="registro">
                            <div class="head-form">
                                <h4>Registro</h4>
                            </div>
                            <div class="body-form">
                                @guest
                                    <form class="row" method="POST" action="{{ route('register') }}">
                                @else
                                    <form class="row" method="POST" action="#">
                                @endguest
                                    {{ csrf_field() }}
                                    <div class="form-group col-md-12{{ $errors->has('email') ? ' has-error' : '' }}">
                                        <label for="email" class="control-label">Correo Electrónico</label>

                                        <div>
                                            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>

                                            @if ($errors->has('email'))
                                                <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="form-group col-md-6{{ $errors->has('name') ? ' has-error' : '' }}">
                                        <label for="name" class="control-label">Nombre</label>
                                            <input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" required autofocus>

                                            @if ($errors->has('name'))
                                                <span class="help-block">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-md-6{{ $errors->has('last-name') ? ' has-error' : '' }}">
                                        <label for="last_name" class="control-label">Apellidos</label>
                                            <input id="last_name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" required autofocus>

                                            @if ($errors->has('last_name'))
                                                <span class="help-block">
                                        <strong>{{ $errors->first('last_name') }}</strong>
                                    </span>
                                            @endif
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for="">País</label>
                                        <select name="country" class="selectpicker" id="">
                                            <option value="1">Colombia</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-12{{ $errors->has('password') ? ' has-error' : '' }}">
                                        <label for="password" class="control-label">Contraseña</label>

                                        <div>
                                            <input id="password" type="password" class="form-control" name="password" required>

                                            @if ($errors->has('password'))
                                                <span class="help-block">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group col-md-12">
                                        <label for="password-confirm" class="control-label">Confirmar contraseña</label>

                                        <div>
                                            <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required>
                                        </div>
                                    </div>
                                        @guest
                                            <div class="form-group col-md-12 pt-3">
                                                <button class="btn btn-primary full-width waves-effect waves-light"><strong>REGISTRARSE</strong></button>
                                            </div>
                                        @else
                                            <div class="form-group col-md-12 pt-3">
                                            </div>
                                        @endguest
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


            <section class="partner-beneficios auto_margin">
                <div class="head">
                    <h3>Beneficios TEMM</h3>
                </div>

                <div class="list-beneficios">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="item">
                                <figure><img src="images/ico-partner-01.svg" class="img-fluid" alt=""></figure>
                                <h3>Date a conocer</h3>
                                <p>Cuando eres ordenado tus clientes lo notan y tu lo disfrutas.</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="item">
                                <figure><img src="images/ico-partner-02.svg" class="img-fluid" alt=""></figure>
                                <h3>Trabaja desde cualquier lugar</h3>
                                <p>Deja atras las antiguas herramientas en donde estas amarrado a un solo dispositivo, con nosotros puedes estar atento de tu negocio desde cualquier lugar</p>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="item">
                                <figure><img src="images/ico-partner-03.svg" class="img-fluid" alt=""></figure>
                                <h3>Aumenta tu productividad</h3>
                                <p>Cuando conoces tus gastos y tus ingresos puedes planear mucho mejor que hacer con tu dinero.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    @include('desktop.login.modal_login')
    <!---->
    <p class="height_20"></p>
    <!---->
@endsection
@section('scripts')
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
        var app_e = new Vue({
            el: "#modal_login",
        });

        function openModal(){
            $('#modal_login').modal('show');
        }
    </script>
@endsection
