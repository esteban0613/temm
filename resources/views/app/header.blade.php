<section id="Header" style="background-color: darkblue">
    <div class="bg-fixed">
        <div id="topBar">
            <div class="area-bar auto_margin">
                <div class="flat-mega-pais">
                    <ul class="mcollapse changer">
                        <li class="movil-menu hidden-md-up"><a href="#" class="menu ssm-toggle-nav" title="open nav"> <i class="mdi mdi-menu mdi-22px"></i> <span
                                        class="txt">Menu</span> </a></li>
                    </ul>
                </div>
            </div>
        </div>
        <header>
            <div class="container-fluid">
                <div class="area-header auto_margin">
                    <div class="row">
                        <div class="col-lg-3 col-md-1">
                            <div class="logo"><a href="/home"><img style="width: 120px;" src="images/logo.png" class="img-retina" alt=""></a></div>
                        </div>
                        <div class="col-lg-9 col-md-11">
                            <div class="topMenu">
                                <div class="flat-mega-menu">
                                    <ul class="mcollapse changer">
                                        @guest
                                            <li><a  style="cursor:pointer;" onclick="openModal()" title="Registro gratis" class="cotice">Login</a></li>
                                        @else

                                            <li>     <a href="{{ route('logout') }}"
                                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();" title="Registro gratis" class="cotice">
                                                    Cerrar Sesión
                                                </a>

                                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                    {{ csrf_field() }}
                                                </form>
                                            </li>
                                        @endguest
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
    </div>
    <div class="topshadow clearfix"></div>
</section>

<div class="bg-head"></div>

<aside id="leftNav" class="sideNav">
    <h5>Menu</h5>
    <div class="adv-panel"></div>
</aside>
<div class="ssm-overlay ssm-toggle-nav"></div>