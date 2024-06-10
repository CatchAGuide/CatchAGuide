@if($locale == 'en')
<input type="hidden" name="domain" value="catchaguide.com">
@else
<input type="hidden" name="domain" value="catchaguide.de">
@endif

<div class="mb-3">
<label class="form-label">Page</label>
<input type="text" class="form-control" id="page" value="{{old('page',$row->page)}}" name="page">
</div>

<div class="mb-3">
<div class="input-group mb-3">
    <span class="input-group-text bg-secondary" id="basic-addon1">
        @if($locale == 'en')
        https://catchaguide.com/
        @else
        https://catchaguide.de/
        @endif
    </span>
    <input type="text" class="form-control"  aria-label="uri" value="{{old('uri',$row->uri)}}" name="uri">
  </div>
</div>

<div class="mb-3">
<label for="page" class="form-label">Meta Type</label>
<select name="meta_type" class="form-select">
    <option {{$row->meta_type ? $row->meta_type == 'title' ? 'selected'  : '' : ''  }} value="title">Title</option>
    <option {{$row->meta_type ? $row->meta_type == 'description' ? 'selected'  : '' : ''  }}  value="description">Description</option>
    <option  {{$row->meta_type ? $row->meta_type == 'keywords' ? 'selected'  : '' : ''  }}  value="keywords">Keywords</option>
</select>
</div>
<div class="mb-3">
<label for="uri" class="form-label">Content</label>
<textarea  class="form-control" name="content" id="" cols="30" rows="5">{{old('content',$row->content)}}</textarea>
</div>

<button type="submit" class="btn btn-primary">Speichern</button>