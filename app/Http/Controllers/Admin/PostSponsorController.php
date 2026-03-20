<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;

class PostSponsorController extends Controller
{
    public function toggle(Post $post): RedirectResponse
    {
        $post->update(['is_sponsored' => ! $post->is_sponsored]);

        $status = $post->is_sponsored ? 'patrocinado' : 'não patrocinado';

        return back()->with('success', "Post marcado como {$status}.");
    }
}
