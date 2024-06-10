<form class="clearfix text-center" action="{{route('pages.contact.store')}}" method="post">
    @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div>{{$error}}</div>
        @endforeach
    @endif
    @csrf
    @method('post')
    <input type="hidden" name="camper_id" value="{{ $camper->id ?? '' }}">
    <div class="quote-block float-left">
        <input type="text" name="name" id="name" placeholder="Name" required>
    </div><!-- /.quote-block -->
    <div class="quote-block float-right">
        <input type="email" name="email" id="email" placeholder="E-Mail Adresse" required>
    </div><!-- /.quote-block -->
    <div class="quote-block float-left">
        <input type="text" name="phone" id="phone" placeholder="Telefonnummer" required>
    </div><!-- /.quote-block -->
    <div class="clr"></div><!-- /.clr -->
    <textarea  name="message" id="message" placeholder="Ihre Nachricht" required></textarea>
    <button type="submit" style="background-color: #05223a">Nachricht senden</button>
</form>
