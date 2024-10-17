
<!-- select -->
<div class="form-group col-md-3">
    <label for="model_type">Type</label>
    <select id="model_type" name="model_type" class="form-control">
        @foreach(\App\Services\ModelService::getModelTypes() as $model => $model_name)
        <option value="{{$model}}">{{$model_name}}</option>
        @endforeach
    </select>
</div>


