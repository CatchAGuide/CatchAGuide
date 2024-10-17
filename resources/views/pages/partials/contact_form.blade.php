<div class="row">
    <div class="col-12 col-sm-12 col-md-12 col-lg-12 my-2">
        <div class="form-group">
            <label class="text-dark fw-bold">Name<span style="color:red;">*</span></label>
          <input type="text" class="form-control @error('name') is-invalid @enderror" wire:model="name">
        </div>
    </div>
        <div class="col-12 col-sm-12 col-md-12 col-lg-6 my-2">
        <div class="form-group">
            <label class="text-dark fw-bold">@lang('search-request.phone')<span style="color:red;">*</span></label>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" wire:model="phone">
        </div>
    </div>
    <div class="col-12 col-sm-12 col-md-12 col-lg-6 my-2">
        <div class="form-group">
            <label class="text-dark fw-bold">@lang('search-request.mail_address')<span style="color:red;">*</span></label>
            <input type="text" class="form-control @error('email') is-invalid @enderror" wire:model="email">
        </div>
    </div>
    <div class="col-12 my-2">
        <div class="form-group">
          <label class="text-dark fw-bold">@lang('search-request.comments')</label>
          <textarea class="form-control" rows="3" wire:model="comments"></textarea>
        </div>
    </div>
</div>

<div class="my-2 d-flex justify-content-between">
    <button type="button" class="btn btn-outline-theme" wire:click="prev('0')">@lang('message.return')</button>
    <button type="submit" class="btn btn-outline-theme">@lang('request.submit')</button>
</div>
