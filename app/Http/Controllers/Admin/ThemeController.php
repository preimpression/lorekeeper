<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use Auth;

use App\Models\Theme;
use App\Models\User\User;

use App\Services\ThemeManager;

use App\Http\Controllers\Controller;
use stdClass;

class ThemeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Admin / Theme Controller
    |--------------------------------------------------------------------------
    |
    | Handles creation/editing of theme categories and themes.
    |
    */

    /**
     * Shows the theme index.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getIndex(Request $request)
    {
        $query = Theme::query();
        $data = $request->only(['name', 'sort']);

        if(isset($data['sort']))
        {
            switch($data['sort']) {
                case 'newest':
                    $query->sortNewest();
                    break;
                case 'oldest':
                    $query->sortOldest();
                    break;
                case 'name_desc':
                    $query->sortAlphabetical(true);
                    break;
                case 'name_asc':
                    $query->sortAlphabetical();
                    break;
            }
        } else {
            $query->sortNewest();
        }

        if(isset($data['name']))
            $query->where('name', 'LIKE', '%'.$data['name'].'%');

        return view('admin.themes.themes', [
            'themes' => $query->paginate(20)->appends($request->query())
        ]);
    }

    /**
     * Shows the create theme page.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getCreateTheme()
    {
        $conditions = new stdClass();
        if (class_exists('\App\Models\Weather\WeatherSeason')) {
            $conditions->seasons =  \App\Models\Weather\WeatherSeason::get()->pluck('name', 'id');
            $conditions->weathers = \App\Models\Weather\Weather::get()->pluck('name', 'id');
        }
        
        return view('admin.themes.create_edit_theme', [
            'theme' => new Theme,
            'conditions' => $conditions
        ]);
    }

    /**
     * Shows the edit theme page.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getEditTheme($id)
    {
        $conditions = new stdClass();
        if (class_exists('\App\Models\Weather\WeatherSeason')) {
            $conditions->seasons =  \App\Models\Weather\WeatherSeason::get()->pluck('name', 'id');
            $conditions->weathers = \App\Models\Weather\Weather::get()->pluck('name', 'id');
        }
        
        $theme = Theme::find($id);
        if(!$theme) abort(404);
        return view('admin.themes.create_edit_theme', [
            'theme' => $theme,
            'conditions' => $conditions
        ]);
    }

    /**
     * Creates or edits an theme.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ThemeManager  $service
     * @param  int|null                  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postCreateEditTheme(Request $request, ThemeManager $service, $id = null)
    {
        $id ? $request->validate(Theme::$updateRules) : $request->validate(Theme::$createRules);
        $data = $request->all();

        if($id && $service->updateTheme(Theme::find($id), $data, Auth::user())) {
            flash('Theme updated successfully.')->success();
        }
        else if (!$id && $theme = $service->createTheme($data, Auth::user())) {
            flash('Theme created successfully.')->success();
            return redirect()->to('admin/themes/edit/'.$theme->id);
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->back();
    }

    /**
     * Gets the theme deletion modal.
     *
     * @param  int  $id
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getDeleteTheme($id)
    {
        $theme = Theme::find($id);
        return view('admin.themes._delete_theme', [
            'theme' => $theme,
        ]);
    }

    /**
     * Creates or edits an theme.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Services\ThemeManager  $service
     * @param  int                       $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postDeleteTheme(Request $request, ThemeManager $service, $id)
    {
        if($id && $service->deleteTheme(Theme::find($id))) {
            flash('Theme deleted successfully.')->success();
        }
        else {
            foreach($service->errors()->getMessages()['error'] as $error) flash($error)->error();
        }
        return redirect()->to('admin/themes');
    }

}
