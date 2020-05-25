<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostRequest;
use App\Post;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PostController extends Controller
{
    function __construct()
    {
        $this->middleware('auth')->except('index', 'show');
    }

    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->search) {
            $posts = DB::table('posts AS p')
                        ->selectRaw('u.*, p.*, p.id AS post_id')
                        ->join('users AS u', 'author_id', '=', 'u.id')
                        ->where('title', 'like', '%'.$request->search.'%')
                        ->orWhere('descr', 'like', '%'.$request->search.'%')
                        ->orWhere('name', 'like', '%'.$request->search.'%')
                        ->orderBy('p.created_at', 'desc')
                        ->get();

            return view('posts.index', ['posts' => $posts]);
        }
        $posts = DB::table('posts AS p')
                    ->selectRaw('u.*, p.*, p.id AS post_id')
                    ->join('users AS u', 'author_id', '=', 'u.id')
                    ->orderBy('p.created_at', 'desc')
                    ->paginate(4);

        return view('posts.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function store(PostRequest $request)
    {
        $post = new Post();
        $post->title = $request->title;
        $post->short_title = Str::length($request->title)>30
            ? Str::substr($request->title, 0, 30) . '...' : $request->title;
        $post->descr = $request->descr;
        $post->author_id = \Auth::user()->id;

        if ($request->file('img')) {
            $path = Storage::putFile('public', $request->file('img'));
            $url = Storage::url($path);
            $post->img = $url;
        }

        $post->save();

        return redirect()->route('post.index')->with('success', 'Пост успешно создан!');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function show($id)
    {
        $post = DB::table('posts AS p')
            ->selectRaw('u.*, p.*, p.id AS post_id')
            ->join('users AS u', 'author_id', '=', 'u.id')
            ->where('p.id', '=', $id)
            ->first();

        if (!isset($post)) {
            return redirect()->route('post.index')->withErrors('Пост не существует');
        }

        return view('posts.show', ['post' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function edit($id)
    {
        $post = DB::table('posts AS p')
            ->selectRaw('u.*, p.*, p.id AS post_id')
            ->join('users AS u', 'author_id', '=', 'u.id')
            ->where('p.id', '=', $id)
            ->first();

        if (!isset($post)) {
            return redirect()->route('post.index')->withErrors('Такой пост не существует');
        }

        if (isset($post->author_id) != \Auth::user()->id) {
            return redirect()->route('post.index')->withErrors('Вы не можете редактировать данный пост');
        }

        return view('posts.edit', ['post' => $post]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function update(PostRequest $request, $id)
    {
        $post = Post::find($id);
        $post->title = $request->title;
        $post->short_title = Str::length($request->title)>30
            ? Str::substr($request->title, 0, 30) . '...' : $request->title;
        $post->descr = $request->descr;

        if (!isset($post)) {
            return redirect()->route('post.index')->withErrors('Такой пост не существует');
        }

        if (isset($post->author_id) != \Auth::user()->id) {
            return redirect()->route('post.index')->withErrors('Вы не можете редактировать данный пост');
        }

        if ($request->file('img')) {
            $path = Storage::putFile('public', $request->file('img'));
            $url = Storage::url($path);
            $post->img = $url;
        }

        $post->update();
        return redirect()->route('post.show', ['post' => $id])->with('success', 'Пост успешно отредактирован!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $post = Post::find($id);

        if (!isset($post)) {
            return redirect()->route('post.index')->withErrors('Пост не существует');
        }
        
        if (isset($post->author_id) != \Auth::user()->id) {
            return redirect()->route('post.index')->withErrors('Вы не можете удалить данный пост');
        }

        $post->delete();

        return redirect()->route('post.index')->with('success', 'Пост успешно удалён!');
    }
}
