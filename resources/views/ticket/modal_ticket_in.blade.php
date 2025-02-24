<div id="modal_ticket_in" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Ingreso de vehiculo</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="min-height: 130px;" >
                <div id="formIn" class="row">
                    <div class="form-group col-md-12">
                        <label for="">Fecha y hora</label>
                        <input id="fecha" type="text" class="form-control" disabled>
                    </div>
                    <div class="form-group col-md-12{{ $errors->has('email') ? ' has-error' : '' }}">
                        <label for="plate" class="control-label">PLACA</label>

                        <div>
                            <input onkeyup="mayus(this);" onkeypress="return verificar(event)"id="plate" type="plate" class="form-control validate[required]" name="plate" value="{{ old('plate') }}" onkeypress="validar(event)" required autofocus autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="">Tipo</label>
                        <select name="type" class="form-control" id="typeIn" {{ \Auth::user()->parking_id==11 ?'disabled':''}}>
                            <option value="1" {!! \Auth::user()->parking_id != 15?'selected':'' !!} >Carro</option>
                            <option value="2">{{ labelMoto() }}</option>
                            @if($typeParking == 2 || $typeParking == 4)
                                <option value="3" {!! \Auth::user()->parking_id==15?'selected':'' !!}>{{ labelTres() }}</option>
                            @endif
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="">Horario</label>
                        <select name="schedule" class="form-control" id="schedule" onchange="mensualidad()">
                            <option value="1" selected >Hora</option>
                            @if(isJornada())
                                <option value="4">Jornada</option>
                            @endif
                            <option value="2">Dia</option>
                            <option value="3">Mes</option>
                        </select>
                    </div>
                    @if(isconvenio())
                    <div class="form-group col-md-6">
                        @php($convenios = App\Convenio::where('parking_id',Illuminate\Support\Facades\Auth::user()->parking_id)->get())
                        <label for="">Convenio</label>
                        <select name="convenio" class="form-control" id="id_convenio">
                            <option value="">Seleccionar</option>
                            @foreach($convenios as $convenio)
                                {!! '<option data-toggle="tooltip" value="'.$convenio->convenio_id.'">'.$convenio->name.'</option>' !!}
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group col-md-12{{ $errors->has('name') ? ' has-error' : '' }}">
                        <label for="drawer" class="control-label">Casilla</label>
                        <input id="drawer" type="text" class="form-control" name="drawer" value="{{ old('name') }}">
                    </div>
                    <div class="form-group col-md-12" id="nameIn">
                        <label for="nombre" class="control-label">Nombre</label>
                        <input onkeyup="mayus(this);" id="nombreIn" type="text" class="form-control validate[required]" name="nombre">
                    </div>
                    <div class="form-group col-md-12" id="priceIn">
                        <label for="nombre" class="control-label">Precio</label>
                        <input id="precioIn" type="number" class="form-control validate[required]" name="price">
                    </div>
                    <div class="form-group col-md-12" id="mailIn">
                        <label for="nombre" class="control-label">Email</label>
                        <input id="emailIn" type="text" class="form-control validate[required]" name="email">
                    </div>
                    <div class="form-group col-md-12" id="movilIn">
                        <label for="nombre" class="control-label">Celular</label>
                        <input id="celularIn" type="number" class="form-control validate[required]" name="nombre">
                    </div>
                    <div class="form-group col-md-12" id="rangeIn">
                        <label for="fechas" class="control-label">Rango fechas</label>
                        <input id="date-range" type="text" class="form-control validate[required]" name="date-range">
                    </div>
                    <div class="form-group col-md-12 pt-3">
                        <button id="new_ticket" class="btn btn-primary full-width waves-effect waves-light" onclick="crearTicket()"><strong>REGISTRAR</strong></button>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
