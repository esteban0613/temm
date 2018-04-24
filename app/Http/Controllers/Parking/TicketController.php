<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Parking;
use App\Ticket;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Html\HtmlServiceProvider;


use PDF; // at the top of the file


class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $now = new Datetime('now');
        $ticket= new Ticket();
        $ticket->hour =$now;
        $ticket->plate =strtoupper($request->plate);
        $ticket->status = 1;
        $ticket->type =$request->type;
        $ticket->schedule =$request->schedule;
        if($request->schedule==3){
            $dateRange = explode(" - ", $request->range);
            $ticket->date_end = new \Carbon\Carbon($dateRange[1]);
            $ticket->name = $request->name;
            $ticket->hour = new \Carbon\Carbon($dateRange[0]);
        }
        $ticket->parking_id = Auth::user()->parking_id;
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->drawer = $request->drawer;
        $ticket->save();


        return $ticket->ticket_id;
    }
    public function pdf(Request $request)
    {
        $id = $request->id_pdf;
        $ticket= Ticket::find($id);
        $hour =new DateTime("".$ticket->hour);
        $hour2 =new DateTime("".$ticket->date_end);
        $style = array(
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => false,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => array(0,0,0),
            'bgcolor' => false, //array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4
        );
        PDF::SetTitle('Ticket');
        PDF::AddPage('P', 'A6');
        PDF::SetMargins(4, 2, 49);
        $parking = Parking::find(Auth::user()->parking_id);
        $html = '<div style="text-align:center"><big style="margin-bottom: 1px"><b>&nbsp; PARQUEADERO '.$parking->name.'</b></big><br>
                <em style="font-size: x-small;margin-top: 2px;margin-bottom: 1px">"Todo lo puedo en Cristo que<br> me fortalece": Fil 4:13 <br></em>
                <small style="font-size: x-small;margin-top: 2px;margin-bottom: 1px"><b>'.$parking->address.'</b></small>'.($parking->parking_id==3?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: Lun-Sab 7am - 9pm</b><br>OLIVEROS HERNANDEZ VALENTINA <br> NIT: 1094965452-1 <br> TEL: 3104276986</small>':'');
        if(!isset($ticket->price)) {
            $html .= '<small style="text-align:left;font-size: small"><b><br>
                 ' . ($ticket->schedule==3? strtoupper($ticket->name) . "<br>" : '') .'
                 Fecha ingreso: ' . $hour->format('d/m/Y') . '<br>
                 Hora ingreso: ' . $hour->format('h:ia') . '<br>
                 ' . ($ticket->schedule==3? "   Fecha vencimiento: " . $hour2->format('d/m/Y') . "<br>" : '') .'
                 ' . ($ticket->schedule==3? strtoupper($ticket->name) . "<br>" : '') .'
                 Tipo: ' . ($ticket->type == 1 ? 'Carro' : 'Moto') . '<br>
                 Placa: ' . $ticket->plate . '<br>
                 ' . (isset($ticket->drawer) ? "Locker: " . $ticket->drawer . "<br>" : '') . '
                 </b></small>
                 <small style="text-align:left;font-size: 6px"><br>
                 1.El vehiculo se entregara al portador de este recibo<br>
                 2.No aceptamos ordenes escritas o por telefono<br>
                 3.Despues de retirado el vehiculo no respondemos por daños, faltas o averias. Revise el vehiculo a la salida.<br>
                 4.No respondemos por objetos dejados en el carro mientras sus puertas esten aseguradas<br>
                 5.No somos responsables por daños o perdidas causadas en el parqueadero mientras el vehiculo no sea entregado personalmente<br>
                 6.No respondemos por la perdida, deterioro o daños ocurridos por causa de incendio, terremoto o causas similares, motin,conmosion civil, revolucion <br>y otros eventos que impliquen fuerza mayor.
                 </small></div>';
        }else{
            $pay_day = new DateTime("".$ticket->pay_day);
            $interval = date_diff($hour,$pay_day);
            $horas = $interval->format("%H");
            $minutos = $interval->format("%I");
            if($minutos<=5 && $horas==0 && $ticket->schedule==1){
                $horas= 0;
            }else{
                $parking = Parking::find(Auth::user()->parking_id);
                $minutos = ($minutos*1) - ($parking->free_time);
                $horas = (24*$interval->format("%d"))+$horas*1 + (($minutos>=0? 1: 0)*1);
                $horas = $horas==0? 1: $horas;
            }
            $html .= '<small style="text-align:left;font-size: small"><br>
                    FACTURA DE VENTA N° ' . $ticket->ticket_id . '<br>
                 ' . ($ticket->schedule==3? strtoupper($ticket->name) . "<br>" : '') .'
                 ' . ($ticket->schedule==1? "   Fracciones: " . $horas . "<br>" : '') .'
                   Fecha ingreso: ' . $hour->format('d/m/Y') . '<br>
                 Hora ingreso: ' . $hour->format('h:ia') . '<br>
                 ' . ($ticket->schedule!=3? "   Fecha salida: " . $pay_day->format('d/m/Y') . "<br>" : '') .'
                 ' . ($ticket->schedule!=3? "   Hora salida: " . $pay_day->format('h:ia') . "<br>" : '') .'
                 ' . ($ticket->schedule==3? "   Fecha vencimiento: " . $hour2->format('d/m/Y') . "<br>" : '') .'
                 Tipo: ' . ($ticket->type == 1 ? 'Carro' : 'Moto') . '<br>
                 Placa: ' . $ticket->plate . '<br>
                 ' . (isset($ticket->price) ? "   Precio: " . $ticket->price . "<br>" : '') .
                (isset($ticket->extra) ? ($ticket->extra>0?"Incremento: ":"Descuento:" ). abs($ticket->extra) . "<br>Total: " . ($ticket->price+$ticket->extra) . "<br>" : '').
                '</small>
</div>';
        }
        $html .= '<small style="text-align:left;font-size: 5px"><br>
                 IMPRESO POR TEMM SOFT 3207329971
                 </small>';
        PDF::writeHTML($html, true, false, true, false, '');
        if(!isset($ticket->price)){
        $id_bar = substr('0000000000'.$ticket->ticket_id,-10);
        PDF::write1DBarcode($id_bar, 'C128C', '', '', '', 18, 0.4, $style, 'N');
        }
        $js = 'print(true);';
        PDF::IncludeJS($js);
        PDF::Output('ticket.pdf');

// set javascript
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function precio($tiempo, $tipo, $schedule)
    {
        $horas = $tiempo->format("%H");
        $minutos = $tiempo->format("%I");
        $parking = Parking::find(Auth::user()->parking_id);
        $minutos = ($minutos*1) - ($parking->free_time);
        $horas = (24*$tiempo->format("%d"))+$horas*1 + (($minutos>=0? 1: 0)*1);
        if($minutos<=5 && $horas==0 && $schedule==1)
            return 0;
        $horas = $horas==0? 1: $horas;
        if($schedule==1)
            return ($tipo==1? $parking->hour_cars_price * $horas: $parking->hour_motorcycles_price * $horas );
        if($schedule==2)
            return ($tipo==1? $parking->day_cars_price: $parking->day_motorcycles_price);
        if($schedule==3)
            return ($tipo==1? $parking->monthly_cars_price: $parking->monthly_motorcycles_price);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $now = new Datetime('now');
        $ticket = Ticket::find($request->ticket_id);
        $interval = date_diff(new DateTime("".$ticket->hour),$now);
        $ticket->status = 2;
        $ticket->price =$this->precio($interval,$ticket->type, $ticket->schedule);
        $ticket->pay_day =$now;
        $ticket->save();
        return [$ticket->price,$interval->format("%H:%I")];
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function getTickets(Request $request)
    {
        $search = $request->get('search')['value'];
        $schedule = $request->get('type');
        $type = $request->get('type_car');
        $range = $request->get('range');
        $status = $request->get('status');

        $tickets= Ticket::select(['ticket_id as Id', 'plate', 'type', 'schedule', 'partner_id', 'status', 'drawer', 'price','hour'])->where('parking_id',Auth::user()->parking_id)->orderBy('ticket_id','desc');
        if ($search) {
                $tickets = $tickets->where('plate', 'LIKE', "%$search%");
        }
        if (!empty($status))
            $tickets = $tickets->where('status', $status);
        if (!empty($schedule))
            $tickets = $tickets->where('schedule', $schedule);
        if (!empty($type))
            $tickets = $tickets->where('type', $type);
        if (!empty($range)){
            $dateRange = explode(" - ", $range);
            $tickets = $tickets->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
        }else{
            $tickets = $tickets->whereBetween('created_at', [ new Datetime('today'), new Datetime('tomorrow')]);
        }
        return Datatables::of($tickets)
            ->addColumn('action', function ($tickets) {
                $htmlAdmin= \Form::button('Editar', [
                        'class'   => 'btn btn-primary',
                        'onclick' => "openModalMod('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Editar !",

                    ]).
                    \Form::button('Eliminar', [
                        'class'   => 'btn btn-warning',
                        'onclick' => "eliminarTicket('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Eliminar !",

                    ]);
                if ($tickets->status == 1)
                return \Form::button('Pagar', [
                        'class'   => 'btn btn-info',
                        'onclick' => "$('#modal_ticket_out').modal('show');$('#ticket_id').val('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Pagar !",

                    ]).(Auth::user()->type == 1?$htmlAdmin:'').
                    \Form::button('Imprimir', [
                        'class'   => 'btn btn-info',
                        'onclick' => "form_pdf('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Imprimir !",

                    ]);
                else
                    return (Auth::user()->type == 1?$htmlAdmin:'').
                        \Form::button('Imprimir', [
                            'class'   => 'btn btn-info',
                            'onclick' => "form_pdf('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Imprimir !",

                        ]).
                        \Form::button('Recuperar', [
                            'class'   => 'btn btn-info',
                            'onclick' => "recuperarTicket('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Recuperar !",

                        ]);
            })
            ->addColumn('Tipo', function ($tickets) {
                return  $tickets->type == 1? 'Carro': 'Moto';
            })
            ->addColumn('entrada', function ($tickets) {
                $hour =new DateTime("".$tickets->hour);
                return  $hour->format('h:ia');
            })
            ->addColumn('Estado', function ($tickets) {
                return  $tickets->status == 1? 'Pendiente Pago': 'Pagó';
            })
            ->addColumn('Atendio', function ($tickets) {
                $partner = Partner::find($tickets->partner_id);
                return  $partner->name;
            })
            ->editColumn('price', function ($tickets) {
                $now = new Datetime('now');
                $interval = date_diff(new DateTime("".$tickets->hour),$now);
                return !empty( $tickets->price)?  $tickets->price: "*".$this->precio($interval,$tickets->type, $tickets->schedule);
            })
            ->make(true);
    }

    public function getMonths(Request $request)
    {
        $search = $request->get('search')['value'];
        $schedule = 3;

        $tickets= Ticket::select(['ticket_id as Id', 'plate', 'type', 'name', 'date_end', 'partner_id', 'status', 'price'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->orderBy('ticket_id','desc');
        if ($search) {
            $tickets = $tickets->where('plate', 'LIKE', "%$search%");
        }
        if (!empty($schedule))
            $tickets = $tickets->where('schedule', $schedule);

        return Datatables::of($tickets)
            ->addColumn('action', function ($tickets) {
                if (Auth::user()->type == 1)
                    return ($tickets->status == 1? \Form::button('Pagar', [
                            'class'   => 'btn btn-info',
                            'onclick' => "$('#modal_ticket_out').modal('show');$('#ticket_id').val('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Pagar !",

                        ]) : "").\Form::button('Editar', [
                        'class'   => 'btn btn-primary',
                        'onclick' => "openModalMod('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Editar !",

                    ]).
                        \Form::button('Eliminar', [
                            'class'   => 'btn btn-warning',
                            'onclick' => "eliminarTicket('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Eliminar !",

                        ]).
                        \Form::button('Imprimir', [
                            'class'   => 'btn btn-info',
                            'onclick' => "form_pdf('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Imprimir !",

                        ]).
                        \Form::button('Renovar', [
                            'class'   => 'btn btn-info',
                            'onclick' => "renovarTicket('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Renovar !",

                        ]);
                else
                    return '';
            })
            ->addColumn('Tipo', function ($tickets) {
                return  $tickets->type == 1? 'Carro': 'Moto';
            })
            ->addColumn('Estado', function ($tickets) {
                $now = date("Y-m-d H:i:s");
                return  $tickets->date_end >= $now? 'Activo': 'Vencido';
            })
            ->addColumn('Atendio', function ($tickets) {
                $partner = Partner::find($tickets->partner_id);
                return  $partner->name;
            })
            ->make(true);
    }
    public function getStatus(Request $request)
    {
        $schedule = $request->get('type');
        $type = $request->get('type_car');
        $range = $request->get('range');
        $status = $request->get('status');

        $tickets= Ticket::select(['plate', 'type', 'extra', 'schedule', 'price', 'name', 'status', 'date_end'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->orderBy('ticket_id','desc');
        if (!empty($schedule))
        $tickets = $tickets->where('schedule', $schedule);
        if (!empty($status))
        $tickets = $tickets->where('status', $status);
        if (!empty($type))
            $tickets = $tickets->where('type', $type);
        if (!empty($range)){
            $dateRange = explode(" - ", $range);
            $tickets = $tickets->whereBetween('created_at', [$dateRange[0], $dateRange[1]]);
        }else{
            $tickets = $tickets->whereBetween('created_at', [ new Datetime('today'), new Datetime('tomorrow')]);
        }
        $status = [];
        $status['total'] = ZERO;
        $status['extra'] = ZERO;
        $status['carros'] = ZERO;
        $status['motos'] = ZERO;
        $status['month_expire'] = 'Mensualidades por vencer:';
        $status['month_expire_num'] = ZERO;
        $tickets=$tickets->get();
        $now = new Datetime('now');
        foreach ($tickets as $ticket){
            $status['total'] += $ticket->price;
            $status['extra'] += $ticket->extra;
            if($ticket->type == 1)
                $status['carros'] ++;
            if($ticket->type == 2)
                $status['motos'] ++;
        }
        $ticketss= Ticket::select(['plate', 'type', 'extra', 'schedule', 'price', 'name', 'date_end'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->orderBy('ticket_id','desc');
        $ticketss = $ticketss->where('schedule', 3);
        $ticketss=$ticketss->get();
        foreach ($ticketss as $ticket){
            if($ticket->schedule == 3 and !empty($ticket->date_end)){
                $hour2 =new DateTime("".$ticket->date_end);
                $diff=date_diff(new DateTime("".$ticket->date_end), $now);
                $diff=$diff->format("%a");
                if($diff<=2){
                    $status['month_expire'] .= $ticket->name.' ('.$ticket->plate.') Vence '.$hour2->format('d/m/Y');
                    $status['month_expire_num'] ++;
                }
            }
        }
        $status['total'] = format_money($status['total']);
        return $status;
    }
    public function getTicket(Request $request)
    {
        $ticket = Ticket::find($request->ticket_id);
        return $ticket;
    }
    public function updateTicket(Request $request)
    {
        $ticket = Ticket::find($request->ticket_id);
        $now = new Datetime('now');
        $ticket->plate =$request->plate;
        $ticket->type =$request->type;
        $ticket->schedule =$request->schedule;
        if($request->schedule==3){
            $dateRange = explode(" - ", $request->range);
            $ticket->date_end = new \Carbon\Carbon($dateRange[1]);
            $ticket->name = $request->name;
            $ticket->hour = new \Carbon\Carbon($dateRange[0]);
        }
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->extra = $request->extra;
        $ticket->drawer = $request->drawer;
        $ticket->save();
        return ;
    }
    public function deleteTicket(Request $request)
    {
        $ticket = Ticket::find($request->ticket_id);
        $ticket->delete();
        return ;
    }
    public function recoveryTicket(Request $request)
    {
        $ticket = Ticket::find($request->ticket_id);
        $ticket->status = 1;
        $ticket->price =null;
        $ticket->pay_day =null;
        $ticket->save();
        return ;
    }
    public function renovarTicket(Request $request)
    {
        $tickets = Ticket::find($request->ticket_id);
        $tickets->status = 3;
        $tickets->save();

        $now = new Datetime('now');
        $ticket= new Ticket();
        $ticket->hour =$now;
        $ticket->plate =strtoupper($tickets->plate);
        $ticket->status = 1;
        $ticket->type =$tickets->type;
        $ticket->schedule =$tickets->schedule;
        if($tickets->schedule==3){
            $date_end = new \Carbon\Carbon($tickets->date_end);
            $ticket->date_end = $date_end->addMonth();
            $ticket->name = strtoupper($tickets->name);
        }
        $ticket->parking_id = Auth::user()->parking_id;
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->drawer = $tickets->drawer;
        $ticket->save();

        return ;
    }
}
