<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Parking;
use App\Ticket;
use App\Customer;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Illuminate\Html\HtmlServiceProvider;
use Nexmo\Laravel\Facade\Nexmo;
use App\Notifications\Message;

use PDF; // at the top of the file


class CustomerController extends Controller
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
        $ticket= new Customer();
        $ticket->nombre =strtoupper($request->name);
        $ticket->telefono =$request->movil;
        $ticket->cedula =$request->cedula;
        $ticket->observacion =$request->observacion;
        $ticket->email =$request->email??'';
        $ticket->date_tm =$request->tm??null;
        $ticket->date_soat =$request->soat??null;
        $ticket->plate =$request->placa??'';
        $ticket->id_partner =Auth::user()->partner_id;
        $ticket->id_parking =Auth::user()->parking_id;
        $ticket->save();

        /*Nexmo::message()->send([
            'to'   => '573207329971',
            'from' => '573207329971',
            'text' => 'te amo care nalga camila.'
        ]);*/
        return $ticket->ticket_id;
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
        $horas2 = $tiempo->format("%H");
        $minutos = $tiempo->format("%I");
        $minutos2 = $minutos;
        $parking = Parking::find(Auth::user()->parking_id);
        $minutos = ($minutos*1) - ($parking->free_time);
        $horas = (24*$tiempo->format("%d"))+$horas*1 + (($minutos>=0? 1: 0)*1);
        if($parking->parking_id==11){
            $minutos2 = (((24*$tiempo->format("%d"))+$horas2*1)*60)+($minutos2*1)-60;
            $priceMin = $minutos2 > 0?($tipo==1? $parking->min_cars_price*$minutos2: $parking->min_motorcycles_price*$minutos2):0;
            if($schedule==1)
                return ($tipo==1? $parking->hour_cars_price: $parking->hour_motorcycles_price )+$priceMin;
        }
        if($tiempo->format("%I")<=5 && $horas==0 && ($schedule==1 || $schedule==2))
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
        $ticket = Customer::find($request->id);
        $ticket->nombre =strtoupper($request->name);
        $ticket->telefono =$request->movil;
        $ticket->cedula =$request->cedula;
        $ticket->observacion =$request->observacion;
        $ticket->email =$request->email??'';
        $ticket->date_tm =$request->tm??null;
        $ticket->date_soat =$request->soat??null;
        $ticket->plate =$request->placa??'';
        $ticket->save();
        return [$ticket->cedula,$ticket->nombre];
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
    public function getCustomers(Request $request)
    {
        $search = $request->get('search')['value'];

        $tickets= Customer::select(['id_customer as Id', 'nombre', 'cedula', 'telefono', 'observacion', 'email', 'plate', 'date_soat','date_tm'])
        ->where('id_parking',Auth::user()->parking_id)->orderBy('nombre','desc');
        if ($search) {
            $user = Auth::user();
            if($user->type == 7 || $user->type == 8){
                $tickets = $tickets->where('plate', 'LIKE', "%$search%");
            }else
                $tickets = $tickets->where('observacion', 'LIKE', "%$search%");
        }
        return Datatables::of($tickets)
            ->addColumn('action', function ($tickets) {
                $htmlAdmin= \Form::button('Editar', [
                        'class'   => 'btn btn-primary',
                        'onclick' => "openModalClienteMod('$tickets->Id')",
                        'data-toggle' => "tooltip",
                        'data-placement' => "bottom",
                        'title' => "Editar !",

                    ]);
                return $htmlAdmin;
            })
            ->editColumn('date_soat', function($tickets){
                $hour2 =new DateTime("".$tickets->date_soat);
                return $hour2->format('d/m/Y');
            })
            ->editColumn('date_tm', function($tickets){
                $hour2 =new DateTime("".$tickets->date_tm);
                return $hour2->format('d/m/Y');
            })
            ->make(true);
    }

    public function getMonths(Request $request)
    {
        $parking = Parking::find(Auth::user()->parking_id);
        $search = $request->get('search')['value'];
        $schedule = 3;

        $tickets= Ticket::select(['ticket_id as Id', 'plate', 'type', 'name', 'date_end', 'partner_id', 'status', 'price','email','phone'])->where('parking_id',Auth::user()->parking_id)->where('status','<>',"3")->orderBy('ticket_id','desc');
        if ($search) {
            $tickets = $tickets->where('plate', 'LIKE', "%$search%");
        }
        if (!empty($schedule))
            $tickets = $tickets->where('schedule', $schedule);

        return Datatables::of($tickets)
            ->addColumn('action', function ($tickets) use($parking){
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

                        ]).(!empty($tickets->phone)?'<a class="btn btn-success" href="https://api.whatsapp.com/send?phone=57'.$tickets->phone.'&text=Hola%20'.$tickets->name.',parqueadero%20'.$parking->name.'%20le%20saluda%20coordialmente%20y%20le%20informa%20que%20el%20vehiculo%20con%20placa%20'.$tickets->plate.'%20tiene%20pago%20el%20parqueo%20con%20nosotros%20hasta%20la%20fecha:%20'.$tickets->date_end.'" target="_blank">Whatsapp</a>':'');
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
        $status['soat_expire'] = 'SOAT por vencer:';
        $status['soat_expire_num'] = ZERO;
        $status['tm_expire'] = 'Tecnomecanica por vencer:';
        $status['tm_expire_num'] = ZERO;
        $now = new Datetime('now');
        $ticketss= Customer::select(['id_customer as Id', 'nombre', 'cedula', 'telefono', 'observacion', 'email', 'plate', 'date_soat','date_tm'])->where('id_parking',Auth::user()->parking_id)->orderBy('id_customer','desc');
        $ticketss=$ticketss->get();
        $parking = Parking::find(Auth::user()->parking_id);
        foreach ($ticketss as $ticket){
            if(!empty($ticket->date_soat)){
                $hour2 =new DateTime("".$ticket->date_soat);
                $diff=date_diff(new DateTime("".$ticket->date_soat), $now);
                $diff=$diff->format("%a");
                if($diff<=5){
                    $status['soat_expire'] .= $ticket->nombre.' ('.$ticket->plate.') Vence '.$hour2->format('d/m/Y').' telefono: <a href="tel:'.$ticket->telefono.'">'.$ticket->telefono.'</a><br> '.
                    (!empty($ticket->telefono)?'<a class="hidden-sm-down btn btn-success" href="https://web.whatsapp.com/send?phone=57'.$ticket->telefono.'&text=Hola%20'.$ticket->name.',lavadero%20'.$parking->name.'%20le%20saluda%20coordialmente%20y%20le%20informa%20que%20el%20vehiculo%20con%20placa%20'.$ticket->plate.'%20tiene%20SOAT%20hasta%20la%20fecha:%20'.$hour2->format('d/m/Y').'" target="_blank">Whatsapp</a>':'').'<br><br>';
                    $status['soat_expire_num'] ++;
                }
            }
            if(!empty($ticket->date_tm)){
                $hour2 =new DateTime("".$ticket->date_tm);
                $diff=date_diff(new DateTime("".$ticket->date_tm), $now);
                $diff=$diff->format("%a");
                if($diff<=5){
                    $status['tm_expire'] .= $ticket->nombre.' ('.$ticket->plate.') Vence '.$hour2->format('d/m/Y').' telefono: <a href="tel:'.$ticket->telefono.'">'.$ticket->telefono.'</a><br> '.
                    (!empty($ticket->telefono)?'<a class="hidden-sm-down btn btn-success" href="https://web.whatsapp.com/send?phone=57'.$ticket->telefono.'&text=Hola%20'.$ticket->name.',lavadero%20'.$parking->name.'%20le%20saluda%20coordialmente%20y%20le%20informa%20que%20el%20vehiculo%20con%20placa%20'.$ticket->plate.'%20tiene%20tecnomecanica%20hasta%20la%20fecha:%20'.$hour2->format('d/m/Y').'" target="_blank">Whatsapp</a>':'').'<br><br>';
                    $status['tm_expire_num'] ++;
                }
            }
        }
        return $status;
    }
    public function getCustomer(Request $request)
    {
        $ticket = Customer::find($request->cliente_id);
        if(!empty($ticket) && !empty($ticket->date_tm)){
            $hour2 =new DateTime("".$ticket->date_tm);
            $ticket->date_tm = $hour2->format('Y-m-d');
        }
        if(!empty($ticket) && !empty($ticket->date_soat)){
            $hour2 =new DateTime("".$ticket->date_soat);
            $ticket->date_soat = $hour2->format('Y-m-d');
        }
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
            $ticket->email = $tickets->email;
            $ticket->phone = $tickets->movil;
            $ticket->price = $tickets->price;
        }
        $ticket->parking_id = Auth::user()->parking_id;
        $ticket->partner_id = Auth::user()->partner_id;
        $ticket->drawer = $tickets->drawer;
        $ticket->save();

        return ;
    }
    public function getSelect(){
        $customers = Customer::where('id_parking' ,Auth::user()->parking_id)->get();
        $select="<option value=''>Seleccionar</option>";
        foreach ($customers as $customer){
            $select .='<option data-toggle="tooltip" title="'.$customer->observacion.'"value="'.$customer->id_customer.'">'.(!empty($customer->plate)?$customer->plate . ' - ':'').$customer->nombre.'('.$customer->observacion.')</option>';
        }
        return $select;
    }
}
