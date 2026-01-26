<link rel="stylesheet" href="https://rawgit.com/LeshikJanz/libraries/master/Bootstrap/baguetteBox.min.css">
<style>

    .container.gallery-container {
        background-color: #fff;
        color: #35373a;
    }

    .gallery-container p.page-description {
        text-align: center;
        margin: 25px auto;
        color: #999;
    }


    .tz-gallery .lightbox img {
        width: 100%;
        border-radius: 0;
        position: relative;
    }

    .tz-gallery .lightbox:before {
        position: absolute;
        top: 50%;
        left: 50%;
        margin-top: -13px;
        margin-left: -13px;
        opacity: 0;
        color: #fff;
        font-size: 26px;
        font-family: 'Glyphicons Halflings';
        content: '\e003';
        pointer-events: none;
        z-index: 9000;
        transition: 0.4s;
    }


    .tz-gallery .lightbox:after {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        opacity: 0;
        background-color: rgba(46, 132, 206, 0.7);
        content: '';
        transition: 0.4s;
    }

    .tz-gallery .lightbox:hover:after,
    .tz-gallery .lightbox:hover:before {
        opacity: 1;
    }

    .baguetteBox-button {
        background-color: transparent !important;
    }

    @media (max-width: 768px) {
        body {
            padding: 0;
        }
    }
</style>
{{--@dd($p_order->toArray())--}}

<div class="container gallery-container">
    <div class="tz-gallery">
        <div class="row">
            <div class="col-sm-12 col-md-12">
                <a class="lightbox" href="{{ asset('storage/'.PRESRCIPTION_ORDER_FILE_PATH.$p_order->prescription_file)}}">
                    <img src="{{ asset('storage/'.PRESRCIPTION_ORDER_FILE_PATH.$p_order->prescription_file)}}" alt="{{ $p_order->name }}">
                </a>
            </div>
        </div>
    </div>
    <p class="page-description text-center small">
        @if(isset($p_order->phone))
            Phone Number : {{ $p_order->phone  }}  <br>
        @endif

        @if(isset($p_order->address))
            Address : {{ $p_order->address  }} <br>
        @endif

        @if(isset($p_order->name))
            Name : {{ $p_order->name  }}
       @endif
    </p>
   
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/baguettebox.js/1.8.1/baguetteBox.min.js"></script>
<script>
    baguetteBox.run('.tz-gallery');
</script>
