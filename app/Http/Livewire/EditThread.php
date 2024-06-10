<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Thread;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditThread extends Component
{
    use WithFileUploads;

    public $threadid;
    public $title;
    public $body;
    public $author;
    public $category_id;
    public $thumbnailpath;

    public $threadImage;

    public function mount()
    {
        $categories = Category::all();
        if(count($categories) > 0) {
            $this->category_id = Category::all()->first()->id;
        }
    }

    public function render()
    {
        return view('livewire.edit-thread', [
            'categories' => Category::all(),
            'title' => $this->title,
            'imagepath' => $this->thumbnailpath
        ]);
    }

    public function updatedThreadImage()
    {
        $this->validate([
            'threadImage' => ['image', 'nullable']
        ]);
    }

    public function update()
    {
        /*$this->validate([
            'title' => ['required', 'string'],
            'body' => ['required', 'min:30'],
            'author' => ['required', 'string'],
            'category_id' => ['required'],
            #'threadImage' => ['required']
        ]);*/
        if($this->threadImage) {
            $thumbnail_path = $this->threadImage->store('public');
        }


        Thread::find($this->threadid)->update([
            'title' => $this->title,
            'body' => $this->body,
            'author' => $this->author,
            'category_id' => $this->category_id,
        ]);
        if($this->threadImage) {
            Thread::find($this->threadid)->update([
                'thumbnail_path' => $thumbnail_path,
            ]);
        }
    }

    public function save()
    {
        $this->validate([
            'title' => ['required', 'string'],
            'body' => ['required', 'min:30', 'string'],
            'author' => ['required', 'string'],
            'category_id' => ['required'],
            'threadImage' => ['required']
        ]);
        $thumbnail_path = $this->threadImage->store('public');

        Thread::create([
            'title' => $this->title,
            'body' => $this->body,
            'author' => $this->author,
            'category_id' => $this->category_id,
            'thumbnail_path' => $thumbnail_path
        ]);


        return redirect()->route('admin.blog.threads.index');
    }
}
