<div class="bg-secondary p-2 badge-primary text-center small my-md-3">
    USERS ONLINE: {{ App\Models\User\User::where('last_seen', '>=', Carbon\Carbon::now()->subMinutes(5))->count() }}
</div>
