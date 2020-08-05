<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Auth;
use Str;

use App\Models\News;
use App\Services\NewsService;

use App\Http\Controllers\Controller;

class BulletinsController extends Controller
{


        /**
         * Shows the news index.
         *
         * @return \Illuminate\Contracts\Support\Renderable
         */
        public function getIndex()
        {
            return view('bulletins.index', ['newses' => News::where('staff_bulletin', 1)->visible()->orderBy('id', 'DESC')->paginate(10)]);
        }

        /**
         * Shows the news index.
         *
         * @return \Illuminate\Contracts\Support\Renderable
         */
        public function getAllIndex()
        {
            return view('bulletins.all_bulletins', ['newses' => News::where('staff_bulletin', 1)->visible()->orderBy('id', 'DESC')->paginate(30)]);
        }


    /**
     * Shows admin bulletins.
     *
     * @param  int          $id
     * @param  string|null  $slug
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getBulletins($id, $slug = null)
    {
        $news = News::where('staff_bulletin', 1)->where('id', $id)->where('is_visible', 1)->first();

        if(!$news) abort(404);

        return view('bulletins.bulletins', ['bulletins' => $news]);
    }




    /**
     * Shows the create news page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateBulletins()
    {

        return view('admin.bulletins.create_edit_bulletin', [
            'bulletins' => new News
        ]);
    }

    /**
     * Shows the edit news page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditBulletins($id)
    {
        $news = News::find($id);
        if(!$news) abort(404);
        return view('admin.bulletins.create_edit_bulletin', [
            'bulletins' => $news
        ]);
    }

    /**
     * Creates or edits a news page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\NewsService  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditBulletins(Request $request, NewsService $service, $id = null)
    {
        $id ? $request->validate(News::$updateRules) : $request->validate(News::$createRules);
        $data = $request->only([
            'title', 'text', 'post_at', 'is_visible', 'staff_bulletin'
        ]);

        if($id && $service->updateNews(News::find($id), $data, Auth::user())) {
            flash('Bulletin updated successfully.')->success();
        }
        else if (!$id && $news = $service->createNews($data, Auth::user())) {
            flash('Bulletin created successfully.')->success();
            return redirect()->to('admin/bulletins/edit/'.$news->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the news deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteBulletins($id)
    {
        $news = News::find($id);
        return view('admin.bulletins._delete_bulletin', [
            'bulletins' => $news,
        ]);
    }

    /**
     * Deletes a news page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\NewsService  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteBulletins(Request $request, NewsService $service, $id)
    {
        if($id && $service->deleteNews(News::find($id))) {
            flash('Bulletin deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/news');
    }


}
