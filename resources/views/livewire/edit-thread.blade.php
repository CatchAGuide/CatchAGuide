<div>
    <form wire:submit.prevent="update">
        <div class="card-body">
            @if($threadImage)
                <div class="row">
                    <div class="col-12">
                        <img src="{{ $threadImage->temporaryUrl() }}" style="width: 300px;">
                    </div>
                </div>
            @else
                <div class="row">
                    <div class="col-12">
                        <img src="{{$imagepath}}" style="width: 300px;">
                    </div>
                </div>
            @endif

            @if ($errors->any())
                @foreach ($errors->all() as $error)
                    <div>{{$error}}</div>
                @endforeach
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="form-group">
                        <label for="threadImage">Thumbnail</label><br/>
                        <input id="threadImage" type="file" wire:model="threadImage">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="form-group">
                        <label for="title">Titel</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Titel des Beitrags" wire:model="title" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="body">Inhalt</label>
                <textarea id="body" cols="30" rows="10" class="form-control" name="body" wire:model="body"></textarea>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-12">
                    <div class="form-group">
                        <label for="autor">Autor</label>
                        <input type="text" class="form-control" id="autor" name="autor" wire:model="author" placeholder="Autor des Beitrags" required>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="category">Kategorie</label>
                <select id="category" name="category_id" class="form-control" wire:model="category_id">
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-success my-1">Speichern</button>
        </div>
    </form>
</div>
