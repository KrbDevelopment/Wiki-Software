<?php

namespace App\Http\Controllers\API\Post;

use App\Http\Controllers\BaseController;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Http\Resources\PostCollection;
use App\Http\Resources\Post as PostResource;

use App\Models\PostHistory;
use App\Http\Resources\PostHistoryCollection;
use App\Http\Resources\PostHistory as PostHistoryResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends BaseController
{
    protected $model = Post::class;
    protected $resource = PostResource::class;
    protected $collection = PostCollection::class;

    protected $validations_create = [
        'title' => 'required|max:255',
        'content' => '',
        'thumbnail' => 'string|max:255'
    ];

    public function update(Request $request, $post_id) {
        $post = Post::find($post_id);

        if (is_null($post)) {
            return $this->sendError('Post does not exists.');
        }

        if (!auth()->user()->hasPermission('posts_update') && $post->user_id !== auth()->user()->id) {
            return $this->sendError('Access denied.', []);
        }

        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required|max:255',
            'content' => '',
            'approve' => 'boolean',
            'thumbnail' => 'string|max:255'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', ['errors' => $validator->errors()], 400);
        }

        $old_post = $post;
        PostHistory::create([
            'post_id' => $post->id,
            'user_id' => $post->user_id,
            'title' => $post->title,
            'content' => $post->content,
            'thumbnail' => $post->thumbnail
        ]);

        $post->title = $input['title'];
        $post->content = $input['content'];
        $post->thumbnail = $input['thumbnail'];

        if ($request->has('approve') && auth()->user()->hasPermission('posts_approve')) {
            $post->approved_by = auth()->user()->id;
            $post->approved_at = now();
        }

        $post->save();

        return $this->sendResponse([
            'post' => new PostResource($post),
            'history_post' => new PostResource($old_post)
        ], 'Post updated successfully.');
    }

    public function get_unauthorized_posts(Request $request) {
        /**
         * Sort Indices
         *
         * 0 - SORT_REGULAR
         * 1 - SORT_STRING
         * 3 - SORT_DESC
         * 4 - SORT_ASC
         * 5 - SORT_LOCALE_STRING
         * 6 - SORT_NATURAL
         * 8 - SORT_FLAG_CASE
         */
        $validator = Validator::make($request->all(), [
            'per_page' => 'integer',
            'paginate' => 'boolean',
            'sort' => 'array',
            'sort.column' => 'string|required_with:sort',
            'sort.method' => 'integer|required_with:sort',
            'additional' => 'array',
            'recent' => 'integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', ['errors' => $validator->errors()], 400);
        }

        $data = $this->model::where('approved_by', null);

        $per_page = $request->get('per_page', 15);
        $paginate_data = $request->get('paginate', true);
        $recent = $request->get('recent', 0);

        if ($recent > 0) {
            $data = $data->sortBy('updated_at', SORT_ASC)->take($recent);
        }

        if ($request->has('sort')) {
            $data = $data->sortBy(
                $request->get('sort.column', 'id'),
                $request->get('sort.method', SORT_ASC)
            );
        }

        if ($paginate_data) {
            $data = $data->paginate($per_page);
        } else {
            $data = $data->get();
        }

        $response = (new $this->collection($data));

        if ($request->has('additional')) {
            $additional = $request->get('additional');

            $response = $response::additional(array_merge([
                'success' => true,
                'message' => 'Successfully retrieved announcements'
            ],
                $additional));
        }

        return $response;
    }
}
