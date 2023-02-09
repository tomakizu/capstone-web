<nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="/">Traffic Congestion Prediction</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse"
            aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse"> 
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item">
                    <a class="nav-link @if($name == 'home') active @endif" aria-current="page" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if($name == 'pems_bay') active @endif" href="/pems_bay">PEMS_BAY</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link @if($name == 'metr_la') active @endif" href="/metr_la">METR_LA</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
