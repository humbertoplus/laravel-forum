<?php

namespace TeamTeaTime\Forum\Http\Controllers\Frontend;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Gate;
use TeamTeaTime\Forum\Events\UserViewingCategory;
use TeamTeaTime\Forum\Events\UserViewingIndex;
use TeamTeaTime\Forum\Http\Requests\UpdateCategory;
use TeamTeaTime\Forum\Http\Requests\StoreCategory;
use TeamTeaTime\Forum\Models\Category;
use TeamTeaTime\Forum\Support\Frontend\Forum;

class CategoryController extends BaseController
{
    public function index(Request $request): View
    {
        $categories = Category::defaultOrder()->get()->filter(function ($category)
        {
            if ($category->is_private) return Gate::allows('view', $category);

            return true;
        })->toTree();

        event(new UserViewingIndex($request->user()));

        return view('forum::category.index', compact('categories'));
    }

    public function show(Request $request, Category $category): View
    {
        if ($category->is_private && ! $request->user() || ! $request->user()->can('view', $category)) abort(404);

        event(new UserViewingCategory($request->user(), $category));

        $categories = $request->user() && $request->user()->can('moveCategories') ? Category::topLevel()->withDepth()->get() : [];

        $threads = $request->user() && $request->user()->can('viewTrashedThreads') ? $category->threads()->withTrashed() : $category->threads();
        $threads = $threads->orderBy('pinned', 'desc')->orderBy('updated_at', 'desc')->paginate();

        return view('forum::category.show', compact('categories', 'category', 'threads'));
    }

    public function store(StoreCategory $request): RedirectResponse
    {
        $category = $request->fulfill();

        Forum::alert('success', 'categories.created');

        return redirect(Forum::route('category.show', $category));
    }

    public function update(UpdateCategory $request): RedirectResponse
    {
        $category = $request->fulfill();

        Forum::alert('success', 'categories.updated', 1);

        return redirect(Forum::route('category.show', $category));
    }

    public function destroy(DestroyCategory $request): RedirectResponse
    {
        $request->fulfill();

        Forum::alert('success', 'categories.deleted', 1);

        return redirect(config('forum.routing.prefix'));
    }

    public function manage(Request $request): View
    {
        $categories = Category::defaultOrder()->get();
        $categories->makeHidden(['_lft', '_rgt', 'thread_count', 'post_count']);

        return view('forum::category.manage', ['categories' => $categories->toTree()]);
    }
}
