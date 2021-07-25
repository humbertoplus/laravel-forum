<?php

// Categories
$r->group(['prefix' => 'category', 'as' => 'category.'], function ($r)
{
    $r->get('/', ['as' => 'index', 'uses' => 'CategoryController@index']);
    $r->get('{category}', ['as' => 'fetch', 'uses' => 'CategoryController@fetch']);
    $r->post('/', ['as' => 'store', 'uses' => 'CategoryController@store']);
    $r->patch('{category}', ['as' => 'update', 'uses' => 'CategoryController@update']);
    $r->delete('{category}', ['as' => 'delete', 'uses' => 'CategoryController@destroy']);
});

// Threads
$r->group(['prefix' => 'thread', 'as' => 'thread.'], function ($r)
{
    $r->get('recent', ['as' => 'recent', 'uses' => 'ThreadController@recent']);
    $r->get('unread', ['as' => 'unread', 'uses' => 'ThreadController@unread']);
    $r->patch('unread/mark-as-read', ['as' => 'unread.mark-as-read', 'uses' => 'ThreadController@markAsRead']);
    $r->get('/', ['as' => 'index', 'uses' => 'ThreadController@index']);
    $r->get('{thread}', ['as' => 'fetch', 'uses' => 'ThreadController@fetch']);
    $r->get('{thread}/posts', ['as' => 'posts', 'uses' => 'ThreadController@posts']);
});

// Bulk actions
$r->group(['prefix' => 'bulk', 'as' => 'bulk.', 'namespace' => 'Bulk'], function ($r)
{
    // Categories
    $r->group(['prefix' => 'category', 'as' => 'category.'], function ($r)
    {
        $r->post('manage', ['as' => 'manage', 'uses' => 'CategoryController@manage']);
    });
});

$r->bind('category', function ($value)
{
    return \TeamTeaTime\Forum\Models\Category::find($value);
});

$r->bind('thread', function ($value)
{
    $thread = \TeamTeaTime\Forum\Models\Thread::withTrashed()->with('category')->find($value);

    if ($thread->trashed() && ! Gate::allows('viewTrashedThreads')) return null;

    return $thread;
});

$r->bind('post', function ($value)
{
    $post = \TeamTeaTime\Forum\Models\Post::withTrashed()->with(['thread', 'thread.category'])->find($value);

    if ($post->trashed() && ! Gate::allows('viewTrashedPosts')) return null;

    return $post;
});