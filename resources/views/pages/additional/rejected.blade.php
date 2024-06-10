@extends('layouts.app')

@section('title', 'Booking Form Reject')

@section('content')


    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

    <style>
        html,body {
            font-family: 'Raleway', sans-serif;
        }
        .thankyou-page ._header {
            background: var(--thm-primary);
            padding: 100px 30px;
            text-align: center;
            background: var(--thm-primary) url(https://codexcourier.com/images/main_page.jpg) center/cover no-repeat;
        }
        .thankyou-page ._header .logo {
            max-width: 200px;
            margin: 0 auto 50px;
        }
        .thankyou-page ._header .logo img {
            width: 100%;
        }
        .thankyou-page ._header h1 {
            font-weight: 800;
            color: white;
            margin: 0;
        }
        .thankyou-page ._body {
            margin: -70px 0 30px;
        }
        .thankyou-page ._body ._box {
            margin: auto;
            max-width: 80%;
            padding: 50px;
            background: white;
            border-radius: 3px;
            box-shadow: 0 0 35px rgba(10, 10, 10,0.12);
            -moz-box-shadow: 0 0 35px rgba(10, 10, 10,0.12);
            -webkit-box-shadow: 0 0 35px rgba(10, 10, 10,0.12);
        }
        .thankyou-page ._body ._box h2 {
            font-size: 32px;
            font-weight: 600;
            color: var(--thm-primary);
        }
    
    </style>
    <!------ Include the above in your HEAD tag ---------->

    <div class="container">
        <div class="thankyou-page">
       
            <div class="_body mt-5 py-5">
                <div class="_box">
                    <p>
                        <h4>@lang('message.booking-reject-header')</h4>
                    </p>
                    <form action="{{route('booking.rejection',$booking)}}" method="POST">
                        @csrf
                        <div class="form-group">
                            <div class="mt-4 mb-3">
                                <p>@lang('message.booking-reject-message')</p>
                                <textarea class="form-control" name="reason" id="" rows="3" required></textarea>
                            </div>
                          <div>
                            <button type="submit" class="thm-btn py-2 my-2">@lang('message.booking-submit')</button>
                          </div>
                        </div>
                      
                    </form>
                </div>
            </div>
        </div>
    </div>



@endsection
