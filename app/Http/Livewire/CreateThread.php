<?php

namespace App\Http\Livewire;

use App\Models\Category;
use App\Models\Thread;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateThread extends Component
{
    use WithFileUploads;

    public $title;
    public $body;
    public $author;
    public $category_id;

    public $threadImage;

    public function render()
    {
        return view('livewire.create-thread', [
            'categories' => Category::all()
        ]);
    }

    public function mount()
    {
        $categories = Category::all();
        if(count($categories) > 0) {
            $this->category_id = Category::all()->first()->id;
        }

    }

    public function updatedThreadImage()
    {
        $this->validate([
            'threadImage' => ['image']
        ]);
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
