<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Parking;
use App\Ticket;
use App\Convenio;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Html\HtmlServiceProvider;
use Nexmo\Laravel\Facade\Nexmo;
use App\Notifications\Message;
use App\Exports\TicketsExport;
use Maatwebsite\Excel\Facades\Excel;

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
        if($request->schedule!=3){
            $count = Ticket::where('plate',strtoupper($request->plate))->where('price',null)->where('parking_id',Auth::user()->parking_id)->count();
            if($count)
                return ;
            $count = Ticket::where('plate',strtoupper($request->plate))->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->where('date_end','>',$now)->count();
            if($count)
                return 'a';
        }
        $ticket= new Ticket();
        $ticket->hour =$now;
        $ticket->plate =strtoupper($request->plate);
        $ticket->status = 1;
        $ticket->type =$request->type;
        $ticket->schedule =$request->schedule;
        if($request->schedule==3){
            $dateRange = explode(" - ", $request->range);
            $ticket->date_end = new \Carbon\Carbon($dateRange[1]);
            $ticket->hour = new \Carbon\Carbon($dateRange[0]);
            $ticket->price = $request->price;
        }
        $ticket->name = $request->name;
        $ticket->email = $request->email;
        $ticket->phone = $request->movil;
        $ticket->parking_id = Auth::user()->parking_id;
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->drawer = $request->drawer;
        $ticket->convenio_id = $request->convenio;
        $ticket->save();

        /*Nexmo::message()->send([
            'to'   => '573207329971',
            'from' => '573207329971',
            'text' => 'te amo care nalga camila.'
        ]);
        if($ticket->ticket_id > 1500){
            return 1;
        }*/
        return $ticket->ticket_id;
    }
    public function pdf(Request $request)
    {
        error_reporting(0);
        $id = $request->id_pdf;
        $iva = $request->isIva ?? 0;
        if(onlyIva())
            $iva=19;
        $ticket= Ticket::find($id);
        $hour =new DateTime("".$ticket->hour);
        $hour2 =new DateTime("".$ticket->date_end);
        $style = array(
            'position' => 'L',
            'align' => 'L',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => 'L',
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
        PDF::AddPage('P', (isContinue()?'A5':'A6'));
        $marginRight = is58()?(isContinue()?103:60):45;
        $marginLeft = is58()?4:6;
        $titulo = is58()?10:11;
        $size = is58()?'8px':'small';
        PDF::SetMargins($marginLeft, 0, $marginRight);
        $parking = Parking::find(Auth::user()->parking_id);
        if($parking->parking_id == 20 || $parking->parking_id == 7){
            $size = '8px';
        }
        if($parking->parking_id ==19 && false){
            switch ($ticket->partner_id) {
                case 67:
                    $parking->name = $parking->name.' #1';
                    break;
                case 68:
                    $parking->name = $parking->name.' #1';
                    break;
                case 69:
                $parking->name = $parking->name.' #1';
                    break;
            }
        }
        $html = '<div style="text-align:center; margin-top: -10px !important"><big style="margin-bottom: -7px"><b style="letter-spacing: -1 px;font-size: '.$titulo.'">&nbsp;&nbsp; '.($parking->type ==3 || $parking->parking_id ==20 || $parking->parking_id ==24 || $parking->parking_id ==17 ?'':'PARQUEADERO').' '.$parking->name.'</b></big><br>
                '.($parking->parking_id !=3 && $parking->parking_id !=5 && $parking->parking_id !=11 && $parking->parking_id !=13 && $parking->parking_id !=9 && $parking->parking_id !=16 && $parking->parking_id !=18 && $parking->parking_id !=19 && $parking->parking_id !=20 && $parking->parking_id !=22?'<em style="font-size: 7px;margin-top: 2px;margin-bottom: 1px">"Todo lo puedo en Cristo que<br> me fortalece": Fil 4:13 <br></em>':'')
                //.($parking->parking_id ==19?'<em style="font-size: 7px;margin-top: 2px;margin-bottom: 1px">La magia está, en no perder la ternura del alma<br></em>':'')
                .($parking->parking_id==16?'<small style="text-align:center;font-size: 7px">
    <b>POLIZA No. 100835</b><br>PREVISORA SEGUROS</small><br>':'').'
                <small style="font-size: x-small;margin-top: 1px;margin-bottom: 1px"><b>'.$parking->address.($parking->parking_id==3?'<br>Armenia, Q':'').'</b></small>'
            .($parking->parking_id==3?'<small style="text-align:center;font-size: 8px"><br>
    NIT: 1094965452-1 <br>OLIVEROS HERNANDEZ VALENTINA<br> </small><small style="text-align:center;font-size: '.(Auth::user()->parking_id != 5?'8px':'7px').'"><b>SERVICIO: Lun-Sab 7am - 8pm</b><br> <b> TEL: 310 5385826</b></small>':'')
            .($parking->parking_id==4?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: Lun-Sab 7am - 9pm</b><br>CARLOS E. MIDEROS <br> NIT: 80449231-4 <br> TEL: 9207119<br> CEL: 3013830790</small>':'').
            ($parking->parking_id==11?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: Lun-Sab 6am - 9pm Dom-Fest 9am - 6pm</b><br>SOLUCIONES Y LOGÍSTICA SAS <br> NIT: 901305901-1 <br> autonorteparking@gmail.com</small>':'').
            ($parking->parking_id==9?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: 24 horas</b><br>INVERSIONES Y CONTRUCCIONES BARI SAS <br> NIT: 901.008.443-4 <br> CEL. 3007216502</small>':'').
            ($parking->parking_id==12?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: 24 horas</b><br>GERMAN ROJAS QUIÑONES <br> NIT: 13870919 <br> TEL: 6717705</small>':'').
            ($parking->parking_id==13?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: LUN-DOM 7AM A 12AM</b><br>sociedad  intermediaria de  negocios s.a.s<br> NIT: 900799396-5 <br></small>':'').
            ($parking->parking_id==14?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: LUN-SAB 7AM A 7PM</b><br>ARMANDO RINCÓN<br> NIT: 13805107-2 <br> CEL. 314 6319341</small>':'').
            ($parking->parking_id==15?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: 24 HORAS</b><br>JIMMY ALBERTO ROA<br> NIT: 74754297-5 <br> CEL. 3229146244 - 31840822321</small>':'').
            ($parking->parking_id==16?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: 24 HORAS</b><br>CESAR MOTTA<br> NIT: 1020732037-7 <br> TEL. 8830896 </small>':'').
            ($parking->parking_id==5?'<small style="text-align:center;font-size: 6px"><br>
    NIT: 89000746-1 <br>&nbsp; HUGO ALEXANDER VARGAS SANCHEZ<br> </small><small style="text-align:center;font-size: 6px"><b>&nbsp;&nbsp;SERVICIO: Lun-Sab 6am - 8pm</b><br> <b> TEL: 3173799831</b></small>':'').
            ($parking->parking_id==7?'<small style="text-align:center;font-size: 6px"><br>
    NIT: 1041325245-3 <br>JHON DEIVID SANTA PULIDO<br> </small><small style="text-align:center;font-size: 8px"><b>SERVICIO: 24 HORAS</b><br> <b> TEL: 3217463250</b></small>':'').
    ($parking->parking_id==19?'<small style="text-align:center;font-size: 8px"><br>
    NIT: 1002901429-0 <br></small><small style="text-align:center;font-size: 8px"><b>SERVICIO: Lun-Sab 6am a 9pm</b><br> <b> TEL: 301 306 8968</b></small>':'').
    ($parking->parking_id==20?'<small style="text-align:center;font-size: 6px"><br>
    NIT: 901451294-1 <br> </small><small style="text-align:center;font-size: 8px"><b>SERVICIO: 7AM - 6PM</b><br> <b> TEL: 8168997</b></small>':'').
            ($parking->parking_id==18?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: LUN-SAB 6AM A 7PM</b><br> TEL. 7716249</small>':'').
            ($parking->parking_id==22?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: 24 HORAS</b><br>DIANA A MUÑOZ L<br> NIT: 52.232.943-5 <br> CEL. 3134098294</small>':'').
            ($parking->parking_id==24?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: 24 HORAS</b><br>EDWIN A SANCHEZ L<br> NIT: 890924840-5 <br> CEL. 3113077583</small>':'').
            ($parking->parking_id==17?'<small style="text-align:center;font-size: 7px"><br>
    <b>SERVICIO: Lun-Jue 5am a 10pm, <br>Vie 5am a 9pm, sab 6am a 8pm, <br>Dom - Festivos 6am a 7pm</b><br>CATALINA GARCIA<br> NIT: 53098598 <br> CEL. 3003352126 </small>':'');
        if(!isset($ticket->price)) {
            $html .= '<small style="text-align:left;font-size: '.$size.';margin-bottom: 1px;"><b><br>
                 ' . ($ticket->schedule==3 || $parking->parking_id==11? "RECIBO N° " . $ticket->ticket_id . "<br>" : '') .'
                 Fecha ingreso: ' . $hour->format('d/m/Y') . '<br>
                 Hora ingreso: ' . $hour->format('h:ia') . '<br>
                 ' . ($ticket->schedule==3? "   Fecha vencimiento: " . $hour2->format('d/m/Y') . "<br>" : '') .'
                 ' . ($ticket->schedule==3? "<b>".strtoupper($ticket->name) . "</b><br>" : '') .'
                 '.($parking->type ==3?'':'Tipo: ' . ($ticket->type == 1 ? 'Carro' : ($ticket->type == 3 ? ( labelTres() ) : labelMoto())) . '<br>').'
                 <small style="text-align:left;font-size:small;letter-spacing: 0.5 px;">'.($parking->type ==3?'Casillero':'Placa').': ' . $ticket->plate . '</small><br>
                 ' . (isset($ticket->drawer) ? "Locker: " . $ticket->drawer . "<br>" : '') . '
                 </b></small>
                 '.($parking->parking_id==3 || $parking->parking_id==25 || $parking->type ==3 || $parking->parking_id ==20?'':'
                 <small style="text-align:left;font-size: 6px;margin-top: 1px">
                 Debe conservar el tiquete para la entrega de su vehículo, en caso de perdida deberá presentar la tarjeta de propiedad del vehículo y la cédula de quien lo retira.<br>
                 1.El vehiculo se entregara al portador de este recibo<br>
                 2.No aceptamos ordenes escritas o por telefono<br>
                 3.Despues de retirado el vehiculo no respondemos por daños, faltas o averias. Revise el vehiculo a la salida.<br>
                 4.No respondemos por objetos dejados en el carro mientras sus puertas esten aseguradas.'.($parking->parking_id!=17?'<br>
                 5.No somos responsables por daños o perdidas causadas en el parqueadero mientras el vehiculo no sea entregado personalmente<br>':'').
                 ($parking->parking_id!=16 && $parking->parking_id!=17?'6.No respondemos por la perdida, deterioro o daños ocurridos por causa de incendio, terremoto o causas similares, motin,conmosion civil, revolucion <br>y otros eventos que impliquen fuerza mayor.':($parking->parking_id!=17?'6.Observaciones: <br><br><br>':'')).
                 '</small>').($parking->parking_id == 20?'<small style="text-align:left;font-size: 5px;margin-top: 1px"><b>POLIZA SEGUROS DEL ESTADO # 33-02-101007580 del 22 -04- 2021 al 21- 04- 2022 </b><br>
                 La empresa no responde por vehículos cuya entrada al sitio de estacionamiento, el usuario no tenga la debida constancia por perdidas, 
                 daños o deterioros ocurridos en los vehículos como consecuencia de incendio, motín, asonada, terremoto, revolución armada, caso fortuito o 
                 fuerza mayor o causa sin culpa de la empresa legalmente, por daños que no dejen constancia al retirar el vehículo, por daños ocasionados a otros vehículos, 
                 si el daño es causado por su propietario o persona ajena al rol del empleado. Por objetos, joyas, maletines, etc. Que no sean entregados en la administración.<br>
                 Procedimiento para reclamaciones: Poner en conocimiento del empleado de turno para que se comunique con el supervisor, Radicar carta en máximo 3 días hábiles con 2 cotizaciones en la Calle 18 #28 A-32 de lunes a viernes de 7am a 5pm</small>':'').'</div>';
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
            $html .= '<small style="text-align:left;font-size: small"><br>'.
                    (isIva()?'REGIMEN COMÚN <br>':'').'FACTURA DE VENTA N° ' . $ticket->ticket_id . '<br>
                 ' . ($ticket->schedule==3?"<b>".strtoupper($ticket->name) . "</b><br>" : '') .'
                 ' . ($ticket->schedule==1? "   Fracciones: " . $horas . "<br>" : '') .'
                 Fecha ingreso: ' . $hour->format('d/m/Y') . '<br>
                 Hora ingreso: ' . $hour->format('h:ia') . '<br>
                 ' . ($ticket->schedule!=3? "   Fecha salida: " . $pay_day->format('d/m/Y') . "<br>" : '') .'
                 ' . ($ticket->schedule!=3? "   Hora salida: " . $pay_day->format('h:ia') . "<br>" : '') .'
                 ' . ($ticket->schedule!=3? "   Duración: " . $interval->format('%d D %h:%i') . "<br>" : '') .'
                 ' . ($ticket->schedule==3? "   Fecha vencimiento: " . $hour2->format('d/m/Y') . "<br>" : '') .'
                 '.($parking->type ==3?'':'Tipo: ' . ($ticket->type == 1 ? 'Carro' : ($ticket->type == 3 ? ( labelTres() ) : labelMoto())) . '<br>').'
                 '.($parking->type ==3?'Casillero':'Placa').': ' . $ticket->plate . '<br>
                 ' . (isset($ticket->price) && empty($iva)? "   Precio: " . $ticket->price . "<br>" : (isset($ticket->price) && !empty($iva)?'
                VALOR SIN IVA: '.intval($ticket->price/1.19).'<br>'.
                    (isset($ticket->extra) ? ($ticket->extra>0?"Incremento: ":"Descuento:" ). abs($ticket->extra).'<br>':'').'
                VALOR IVA           : '.(($ticket->price+$ticket->extra)-intval(($ticket->price+$ticket->extra)/1.19)).'<br>
                VALOR TOTAL         : '.(($ticket->price+$ticket->extra)):'')) .
                ($parking->parking_id==24?"<small style='text-align:left;font-size: 13px'><br>Si desea factura electronica favor comunicarse al número 3233213075 durante las proximas 24 horas</small>":"").
                (isset($ticket->extra) && empty($iva)? ($ticket->extra>0?"<br>Incremento: ":"<br>Descuento:" ). abs($ticket->extra) . "<br>Total: " . ($ticket->price+$ticket->extra) . "<br>" : '').
                '</small>
</div>';
        }
        if(!isset($ticket->price) && (Auth::user()->parking_id!=11) && !isBar()){
            $html .='<small style="text-align:center;font-size: 7px">'.
                '<b>TICKET No. </b>'.$ticket->ticket_id.'</small>';
        }
        $html .= ($parking->parking_id==11?'<small style="text-align:center;font-size: 7px">
    <b>POLIZA No. 21-02-101009484</b><br>SEGUROS DEL ESTADO</small><br>':'').'<small style="text-align:left;font-size: 6px;letter-spacing:0.5px">
                 <b>IMPRESO POR TEMM SOFT 3207329971</b>
                 </small>';
        PDF::writeHTML($html.'</br>', true, false, true, false, '');
        if(!isset($ticket->price) && (Auth::user()->parking_id!=11) && isBar()){
            $id_bar = substr('0000000000'.$ticket->ticket_id,-10);
            PDF::write1DBarcode($id_bar, 'C128C', '', '', '', 18, 0.4, $style, 'N');
        }
        $js = 'this.print(true);';
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

    public function precio($tiempo, $tipo, $schedule, $convenio = null, $start = null)
    {
        $horas = $tiempo->format("%H");
        $horas2 = $tiempo->format("%H");
        $minutos = $tiempo->format("%I");
        $dias = $tiempo->format("%d");
        $minutos2 = $minutos;
        $parking = Parking::find(Auth::user()->parking_id);
        $minutos = ($minutos*1) - ($parking->free_time);
        $horas = (24*$tiempo->format("%d"))+$horas*1 + (($minutos>=0? 1: 0)*1);
        $is_convenio = false;
        if(!empty($convenio)){
            $convenio = Convenio::find($convenio);
            $is_convenio = true;
            if(!empty($convenio)){
                if($tipo==1){
                    $parking->min_cars_price = $convenio->min_cars_price ?? $parking->min_cars_price;
                    $parking->hour_cars_price = $convenio->hour_cars_price ?? $parking->hour_cars_price;
                    $parking->day_cars_price = $convenio->day_cars_price ?? $parking->day_cars_price;
                }
                if($tipo==2){
                    $parking->min_motorcycles_price = $convenio->min_motorcycles_price ?? $parking->min_motorcycles_price;
                    $parking->hour_motorcycles_price = $convenio->hour_motorcycles_price ?? $parking->hour_motorcycles_price;
                    $parking->day_motorcycles_price = $convenio->day_motorcycles_price ?? $parking->day_motorcycles_price;
                }
                if($tipo==3){
                    $parking->min_van_price = $convenio->min_van_price ?? $parking->min_van_price;
                    $parking->hour_van_price = $convenio->hour_van_price ?? $parking->hour_van_price;
                    $parking->day_van_price = $convenio->day_van_price ?? $parking->day_van_price;
                }
            }
        }
        if(Auth::user()->parking_id == 1){
            $now = new Datetime('now');
            $startTime = new Datetime($start);
            $rango = $now->format("H");
            $rangoAux = $startTime->format("H");
            $aditional = $rango < 6 || $rango >= 18 ? 1 : 0;
            $aditionalAux = $rangoAux < 6 || $rangoAux >= 18 ? 1 : 0;
            $periods = ($aditional == $aditionalAux ? 1 : 2);
            $dias = intval($horas/12) > 0 ? (intval($horas/12) + ($aditional == $aditionalAux ? 1 : -1)) : 0;
            $noches = intval($horas/12) > 0 ? max(intval(intval($horas/12)/2),1) : (($aditional || $aditionalAux)? 1:0);
            

            return ( $parking->day_cars_price * ($periods + $dias)) + ( $noches *1000 );
            return (((intval($horas/12))+(($horas%12 > 0 ? 1:0)) + (($rango < 6 ? $rango+24 : $rango) - ($horas%24) < 18 ? 1 : 0) )*$parking->day_cars_price)+((intval($horas / 24)) * 1000)+ ( $aditional * 1000 );
        }
        if(Auth::user()->parking_id == 15){
            $schedule=4;
        }
        $dayPrice = ($tipo==1? $parking->day_cars_price : ($tipo==2?$parking->day_motorcycles_price:$parking->day_van_price));
        if(($parking->type==4) && $schedule==1){
            $minutos2 = (((24*$tiempo->format("%d"))+$horas2*1)*60)+($minutos2*1);
            if($is_convenio){
                if(($parking->hour_cars_price<10 && $tipo==1 ) || ($parking->hour_motorcycles_price <10 && $tipo==2) || ($parking->hour_van_price <10 && $tipo==3)){
                    $regalo = ($tipo==1? $parking->hour_cars_price: ($tipo==2? $parking->hour_motorcycles_price: $parking->hour_van_price ));
                    $minutos2 = $minutos2-($regalo*60) < 0? 0 : $minutos2-($regalo*60);
                }
                if(($parking->hour_cars_price<300 && $parking->hour_cars_price >=10 && $tipo==1 ) || ($parking->hour_motorcycles_price <300 && $parking->hour_motorcycles_price >=10 && $tipo==2) || ($parking->hour_van_price <300 && $parking->hour_van_price >=10 && $tipo==3)){
                    $regalo = ($tipo==1? $parking->hour_cars_price: ($tipo==2? $parking->hour_motorcycles_price: $parking->hour_van_price ));
                    $minutos2 = $minutos2-($regalo) < 0? 0 : $minutos2-($regalo);
                }
            }
            $priceMin = $minutos2 > 0?($tipo==1? $parking->min_cars_price*$minutos2: ($tipo==2?$parking->min_motorcycles_price*$minutos2:$parking->min_van_price*$minutos2)):0;
            if($schedule==1 && ($priceMin < $dayPrice || $dayPrice == 0))
                return intval(round($priceMin*1/100)*100);
            else
                if(isJornada())
                    $schedule=4;
                else
                    $schedule=2;
        }
        if($tiempo->format("%I")<=5 && $horas==0 && ($schedule==1 || $schedule==2 || $schedule==4) && $parking->parking_id!=14)
            return 0;
        $horas = $horas==0? 1: $horas;
        if(Auth::user()->parking_id == 9 && empty($convenio)){
            $minutos = $minutos2*1 <= 15 && $tipo==1? 0.25 :($minutos2*1 <= 30 ? 0.5 :($minutos2*1 <= 45 && $tipo==1? 0.75 :1));
            $horas = $horas2 + $minutos;
        }
        if($schedule==1){
            if($is_convenio){
                if(($parking->hour_cars_price<10 && $tipo==1 ) || ($parking->hour_motorcycles_price <10 && $tipo==2) || ($parking->hour_van_price <10 && $tipo==3)){
                    $regalo = ($tipo==1? $parking->hour_cars_price: ($tipo==2? $parking->hour_motorcycles_price: $parking->hour_van_price ));
                    $horas = $horas-$regalo < 0? 0 : $horas-$regalo;
                    $parking = Parking::find(Auth::user()->parking_id);
                    $dayPrice = ($tipo==1? $parking->day_cars_price : ($tipo==2?$parking->day_motorcycles_price:$parking->day_van_price));
                }
            }
            $price= ($tipo==1? $parking->hour_cars_price * $horas: ($tipo==2? $parking->hour_motorcycles_price * $horas: $parking->hour_van_price * $horas ));
            if($price < $dayPrice || $dayPrice == 0 || Auth::user()->parking_id == 3 || Auth::user()->parking_id == 5 || Auth::user()->parking_id == 14 )
                return intval($price);
            else{
                if(isJornada())
                    $schedule=4;
                else
                    $schedule=2;
            }
        }
        if($schedule==2)
            return ($tipo==1? $parking->day_cars_price: ($tipo==2? $parking->day_motorcycles_price: $parking->day_van_price ))* ($dias+1);
        if($schedule==3)
            return ($tipo==1? $parking->monthly_cars_price: ($tipo==2? $parking->monthly_motorcycles_price: $parking->monthly_van_price ));
        if($schedule==4){
            $dias = (intval($horas2/12)+1+($dias*2));
            return (($tipo==1? $parking->day_cars_price: ($tipo==2? $parking->day_motorcycles_price: $parking->day_van_price ))* ($dias));
        }
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
        $ticket->convenio_id = $request->convenio_id??null;
        if($ticket->status == 2 && !empty($ticket->pay_day)){
            $interval = date_diff(new DateTime("".$ticket->hour),new DateTime("".$ticket->pay_day));
            return [$ticket->price,$interval->format("%H:%I")];
        }
        $interval = date_diff(new DateTime("".$ticket->hour),$now);
        $ticket->status = 2;
        $now2 = date("Y-m-d H:i:s");
        $ticketss= Ticket::select(['plate'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->where('plate',$ticket->plate)->where('date_end','>=',$now2)->orderBy('ticket_id','desc')->get();

        if($ticket->schedule != 3 || empty($ticket->price))
            $ticket->price = $this->precio($interval,$ticket->type, $ticket->schedule, $ticket->convenio_id, $ticket->hour);
        if($ticketss->count() > 0 && $ticket->schedule != 3)
            $ticket->price =0;
        $ticket->partner_id = Auth::user()->partner_id;
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
        $partner = $request->get('partner');

        $tickets= Ticket::select(['ticket_id as Id', 'plate', 'type', 'schedule', 'partner_id', 'status', 'drawer', 'price','hour','extra','convenio_id'])->where('parking_id',Auth::user()->parking_id)->orderBy('ticket_id','desc');
        if ($search) {
            $search = strtoupper ($search);
            $tickets = $tickets->where('plate', 'LIKE', "%$search%");
        }
        if (!empty($status))
            $tickets = $tickets->where('status', $status);
        if (!empty($schedule))
            $tickets = $tickets->where('schedule', $schedule);
        if (!empty($type))
            $tickets = $tickets->where('type', $type);
        if (!empty($partner))
            $tickets = $tickets->where('partner_id', $partner);
        if(!(Auth::user()->parking_id == 3 && !empty($search))){
            if (!empty($range)){
                $dateRange = explode(" - ", $range);
                $tickets = $tickets->whereBetween('created_at', [$dateRange[0].' 00:00:00', $dateRange[1].' 23:59:59']);
            }else{
                $tickets = $tickets->whereBetween('created_at', [ new Datetime('today'), new Datetime('tomorrow')]);
            }
        }
        if (Auth::user()->type != 1 && false) {
                $tickets = $tickets->where('partner_id', Auth::user()->partner_id);
            }
        return Datatables::of($tickets)
            ->addColumn('action', function ($tickets) {
                $edit = \Form::button('Editar', [
                    'class'   => 'btn btn-primary',
                    'onclick' => "openModalMod('$tickets->Id')",
                    'data-toggle' => "tooltip",
                    'data-placement' => "bottom",
                    'title' => "Editar !",

                ]);
                $htmlAdmin= $edit.
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
                        'onclick' => "$('#modal_ticket_out').modal('show');$('#id_convenio_pay').val('');$('#ticket_id').val('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Pagar !",

                    ]).(Auth::user()->type == 1?$htmlAdmin:(Auth::user()->type == 2 && isconvenio()?$edit:'')).
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

                        ]).($tickets->schedule != 3?
                        \Form::button('Recuperar', [
                            'class'   => 'btn btn-info',
                            'onclick' => "recuperarTicket('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Recuperar !",

                        ]):'').(isIva()?\Form::button('Imprimir IVA', [
                            'class'   => 'btn btn-info',
                            'onclick' => "form_pdf_iva('$tickets->Id')",
                            'data-toggle' => "tooltip",
                            'data-placement' => "bottom",
                            'title' => "Imprimir !",

                        ]):'');
            })
            ->addColumn('Tipo', function ($tickets) {
                return  $tickets->type == 1? 'Carro': ($tickets->type == 3 ? ( labelTres() ) : labelMoto());
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
                return (isset( $tickets->price)?  format_money($tickets->price):( "*".format_money($this->precio($interval,$tickets->type, $tickets->schedule, $tickets->convenio_id, $tickets->hour)))).($tickets->extra?' ('.format_money($tickets->extra).')':'');
            })
            ->escapeColumns([])
            ->make(true);
    }

    public function getMonths(Request $request)
    {
        $parking = Parking::find(Auth::user()->parking_id);
        $search = $request->get('search')['value'];
        $schedule = 3;

        $tickets= Ticket::select(['ticket_id as Id', 'plate', 'type', 'name', 'date_end', 'partner_id', 'status', 'price','email','phone','extra'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->orderBy('ticket_id','desc');
        if ($search) {
            $search = strtoupper ($search);
            $tickets = $tickets->where('plate', 'LIKE', "%$search%");
        }
        if (!empty($schedule))
            $tickets = $tickets->where('schedule', $schedule);

        return Datatables::of($tickets)
            ->addColumn('action', function ($tickets) use($parking){
                if (Auth::user()->type == 1)
                    return ($tickets->status == 1? \Form::button('Pagar', [
                            'class'   => 'btn btn-info',
                            'onclick' => "$('#modal_ticket_out').modal('show');$('#id_convenio_pay').val('');$('#ticket_id').val('$tickets->Id')",
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

                        ]).(!empty($tickets->phone)?'<a class="hidden-sm-down btn btn-success" href="https://web.whatsapp.com/send?phone=57'.$tickets->phone.'&text=Hola%20'.$tickets->name.',parqueadero%20'.$parking->name.'%20le%20saluda%20coordialmente%20y%20le%20informa%20que%20el%20vehiculo%20con%20placa%20'.$tickets->plate.'%20tiene%20pago%20el%20parqueo%20con%20nosotros%20hasta%20la%20fecha:%20'.$tickets->date_end.'" target="_blank">Whatsapp</a>':'').(!empty($tickets->phone)?'<a class="hidden-md-up btn btn-success" href="https://api.whatsapp.com/send?phone=57'.$tickets->phone.'&text=Hola%20'.$tickets->name.',parqueadero%20'.$parking->name.'%20le%20saluda%20coordialmente%20y%20le%20informa%20que%20el%20vehiculo%20con%20placa%20'.$tickets->plate.'%20tiene%20pago%20el%20parqueo%20con%20nosotros%20hasta%20la%20fecha:%20'.$tickets->date_end.'" target="_blank">Whatsapp</a>':'');
                else
                    return '';
            })
            ->addColumn('Tipo', function ($tickets) {
                return  $tickets->type == 1? 'Carro': labelMoto();
            })
            ->addColumn('Estado', function ($tickets) {
                $now = date("Y-m-d H:i:s");
                return  '<b style="color: '.($tickets->date_end >= $now?'green':'red').';">'.($tickets->date_end >= $now? 'Activo': 'Vencido').'</b><br><b style="color: '.($tickets->status == 1?'red':'green').';">'.($tickets->status == 1?'Sin Pagar':'Pago').'</b>';
            })
            ->editColumn('price', function($tickets){
                return format_money($tickets->price).($tickets->extra?' ('.format_money($tickets->extra).')':'');
            })
            ->editColumn('date_end', function($tickets){
                $hour2 =new DateTime("".$tickets->date_end);
                return $hour2->format('d/m/Y');
            })
            ->addColumn('Atendio', function ($tickets) {
                $partner = Partner::find($tickets->partner_id);
                return  $partner->name;
            })
            ->escapeColumns([])
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
            $tickets = $tickets->whereBetween('created_at', [$dateRange[0].' 00:00:00', $dateRange[1].' 23:59:59']);
        }else{
            $tickets = $tickets->whereBetween('created_at', [ new Datetime('today'), new Datetime('tomorrow')]);
        }
        $status = [];
        $status['total'] = ZERO;
        $status['extra'] = ZERO;
        $status['carros'] = ZERO;
        $status['camioneta'] = ZERO;
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
            if($ticket->type == 3)
                $status['camioneta'] ++;
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
        if($status['camioneta'] > 0)
            $status['motos'] = $status['motos'] .' / '.$status['camioneta'];
        $status['total'] = format_money($status['total']);
        $status['extra'] = format_money($status['extra']);
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
            $ticket->email = $request->email;
            $ticket->phone = $request->movil;
            $ticket->price = $request->price;
        }
        $ticket->convenio_id = $request->convenio;
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->extra = $request->extra;
        $ticket->drawer = $request->drawer;
        $ticket->save();
        return ;
    }
    public function deleteTicket(Request $request)
    {
        $ticket = Ticket::find($request->ticket_id);
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->save();
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
            $ticket->email = $tickets->email;
            $ticket->phone = $tickets->phone;
            $ticket->price = $tickets->price;
        }
        $ticket->parking_id = Auth::user()->parking_id;
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->drawer = $tickets->drawer;
        $ticket->save();

        return ;
    }

    public function export($range){

        if(Auth::user()->type == 1)
            return Excel::download(new TicketsExport($range), 'Reporte_'.$range.'.xlsx');
        else
            $this->reportUser($range);
    }

    public function reportUser($range)
    {
        error_reporting(0);
        $tickets= Ticket::select(['plate', 'type', 'extra', 'schedule', 'price', 'name', 'status', 'date_end'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->orderBy('ticket_id','desc');
        $tickets = $tickets->where('status', 2);
        $tickets = $tickets->where('partner_id', Auth::user()->partner_id);
        $dateRange = explode(" - ", $range);
        $tickets = $tickets->whereBetween('created_at', [$dateRange[0].' 00:00:00', $dateRange[1].' 23:59:59']);

        $status = [];
        $status['total'] = ZERO;
        $status['extra'] = ZERO;
        $status['carros'] = ZERO;
        $status['camioneta'] = ZERO;
        $status['motos'] = ZERO;

        $tickets=$tickets->get();
        foreach ($tickets as $ticket){
            $status['total'] += $ticket->price;
            $status['extra'] += $ticket->extra;
            if($ticket->type == 1)
                $status['carros'] ++;
            if($ticket->type == 2)
                $status['motos'] ++;
            if($ticket->type == 3)
                $status['camioneta'] ++;
        }
        PDF::SetTitle('Reporte PDF');
        PDF::AddPage('P', 'A6');
        $marginRight = Auth::user()->parking_id == 5?57:45;
        $marginLeft = Auth::user()->parking_id == 5?2:6;
        PDF::SetMargins($marginLeft, 0, $marginRight);

        $html = '<table style="width:100%">
        <tr>
        <th>'.$dateRange[0].'</th>
        <th>'.$dateRange[1].'</th> 
        <th>&nbsp;</th>
      </tr>
      <tr>
        <td colspan="1"><b>Usuario</b></td>
        <td><b>'.Auth::user()->name.'</b></td> 
      </tr>
      <tr>
        <td colspan="1"><b>Carros</b></td>
        <td><b>'.$status['carros'].'</b></td> 
      </tr>
      <tr>
        <td colspan="1"><b>'.labelMoto(false).'</b></td>
        <td><b>'.$status['motos'].'</b></td> 
      </tr>
      <tr>
        <td colspan="1"><b>'.(labelTres(false)).'</b></td>
        <td><b>'.$status['camioneta'].'</b></td> 
      </tr>
     
    <tr>
        <td colspan="1"><b>Ventas</b></td>
        <td><b>'.$status['total'].'</b></td> 
      </tr>
      <tr>
        <td colspan="1"><b>Extras</b></td>
        <td><b>'.$status['extra'].'</b></td> 
      </tr>
      <hr>
      <tr>
        <td colspan="1"><b>Saldo</b></td>
        <td><b>'.($status['total']+$status['extra']).'</b></td> 
      </tr>
      <hr>
  </table>';
        PDF::writeHTML($html, true, false, true, false, '');
        $js = 'print(true);';
        PDF::IncludeJS($js);
        PDF::Output('ticket.pdf');
    }
}
