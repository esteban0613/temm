<div id="modal_add" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Nuevo Cliente</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="min-height: 130px;" >
                <div id="formAddCustomer" class="row">
                    <div class="form-group col-md-12">
                        <label for="nombre" class="control-label">Nombre</label>
                        <input onkeyup="mayus(this);" id="nombreCustomer" type="text" class="form-control validate[required]">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="plate" class="control-label">PLACA</label>
                        <div>
                            <input onkeyup="mayus(this);" id="plateCustomer" type="plate" class="form-control validate[required]" value="{{ old('plate') }}" onkeypress="validar(event)" required autofocus autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="plate" class="control-label">SOAT</label>
                        <div>
                            <input id="soatCustomer" type="date" class="form-control" autofocus autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="plate" class="control-label">Tecnomecanica</label>
                        <div>
                            <input id="tmCustomer" type="date" class="form-control" autofocus autocomplete="off">
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        <label for="nombre" class="control-label">Cedula</label>
                        <input id="cedulaCustomer" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="nombre" class="control-label">Celular</label>
                        <input id="celularCustomer" type="number" class="form-control validate[required]">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="nombre" class="control-label">Email</label>
                        <input id="emailCustomer" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-12">
                        <label for="fechas" class="control-label">{{ isLavadero()?'Placa':'Observación' }}</label>
                        <input id="observacionCustomer" type="text" class="form-control">
                    </div>
                    <div class="form-group col-md-12 pt-3">
                        <button id="new_customer" class="btn btn-primary full-width waves-effect waves-light" onclick="crearCliente()"><strong>REGISTRAR</strong></button>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
