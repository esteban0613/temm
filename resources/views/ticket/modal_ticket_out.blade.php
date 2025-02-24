<div id="modal_ticket_out" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Cobrar</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" style="min-height: 130px;" >
                <div class="form-horizontal">
                    {{ csrf_field() }}
                    <div class="form-group{{ $errors->has('plate') ? ' has-error' : '' }}">
                        <input id="ticket_id" name="ticket_id" type="text" placeholder="ticket" onkeypress="validar2(event)">
                    </div>
                    @if(isconvenio())
                    <div class="form-group col-md-12">
                        @php($convenios = App\Convenio::where('parking_id',Illuminate\Support\Facades\Auth::user()->parking_id)->get())
                        <label for="">Convenio</label>
                        <select name="convenio" class="form-control" id="id_convenio_pay">
                            <option value="">Seleccionar</option>
                            @foreach($convenios as $convenio)
                                {!! '<option data-toggle="tooltip" value="'.$convenio->convenio_id.'">'.$convenio->name.'</option>' !!}
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="form-group ">
                        <button id="b_pagar" type="button" onclick="pagar()" class="btn btn-primary full-width">
                            cobrar
                        </button>
                    </div>
                </div>
            </div>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
