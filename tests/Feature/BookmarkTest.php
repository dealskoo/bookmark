<?php

namespace Dealskoo\Bookmark\Tests\Feature;

use Closure;
use Dealskoo\Bookmark\Events\Bookmarked;
use Dealskoo\Bookmark\Events\Unbookmarked;
use Dealskoo\Bookmark\Tests\Post;
use Dealskoo\Bookmark\Tests\Product;
use Dealskoo\Bookmark\Tests\TestCase;
use Dealskoo\Bookmark\Tests\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

class BookmarkTest extends TestCase
{
    use RefreshDatabase;

    public function test_basic_features()
    {
        Event::fake();
        $user = User::create(['name' => 'user']);
        $post = Post::create(['title' => 'test guide']);
        $user->bookmark($post);
        Event::assertDispatched(Bookmarked::class, function ($event) use ($user, $post) {
            $bookmark = $event->bookmark;
            return $bookmark->bookmarkable instanceof Post && $bookmark->user instanceof User && $bookmark->user->id == $user->id && $bookmark->bookmarkable->id == $post->id;
        });
        $this->assertTrue($user->hasBookmarked($post));
        $this->assertTrue($post->isBookmarkedBy($user));
        $this->assertTrue($user->unbookmark($post));

        Event::assertDispatched(Unbookmarked::class, function ($event) use ($user, $post) {
            return $event->bookmark->bookmarkable instanceof Post && $event->bookmark->user instanceof User && $event->bookmark->user->id == $user->id && $event->bookmark->bookmarkable->id == $post->id;
        });
    }

    public function test_unlike_features()
    {
        $user1 = User::create(['name' => 'user1']);
        $user2 = User::create(['name' => 'user2']);
        $user3 = User::create(['name' => 'user3']);
        $post = Post::create(['title' => 'test post']);

        $user1->bookmark($post);
        $user1->bookmark($post);
        $user2->bookmark($post);
        $user3->bookmark($post);

        $user2->unbookmark($post);

        $this->assertFalse($user2->hasBookmarked($post));
        $this->assertTrue($user1->hasBookmarked($post));
        $this->assertTrue($user3->hasBookmarked($post));
        $this->assertCount(1, $user1->bookmarks);
    }

    public function test_aggregations()
    {
        $user = User::create(['name' => 'user']);

        $post1 = Post::create(['title' => 'post1']);
        $post2 = Post::create(['title' => 'post2']);

        $product1 = Product::create(['name' => 'product1']);
        $product2 = Product::create(['name' => 'product2']);

        $user->bookmark($post1);
        $user->bookmark($post2);
        $user->bookmark($product1);
        $user->bookmark($product2);

        $this->assertCount(4, $user->bookmarks);
        $this->assertCount(2, $user->bookmarks()->withType(Post::class)->get());
    }

    public function test_object_bookmarker()
    {
        $user1 = User::create(['name' => 'user1']);
        $user2 = User::create(['name' => 'user2']);
        $user3 = User::create(['name' => 'user3']);

        $post = Post::create(['title' => 'test post']);

        $user1->bookmark($post);
        $user2->bookmark($post);
        $this->assertCount(2, $post->bookmarks);
        $this->assertCount(2, $post->bookmarkers);

        $this->assertSame($user1->name, $post->bookmarkers[0]['name']);
        $this->assertSame($user2->name, $post->bookmarkers[1]['name']);

        $sqls = $this->getQueryLog(function () use ($post, $user1, $user2, $user3) {
            $this->assertTrue($post->isBookmarkedBy($user1));
            $this->assertTrue($post->isBookmarkedBy($user2));
            $this->assertFalse($post->isBookmarkedBy($user3));
        });

        $this->assertEmpty($sqls->all());
    }

    public function test_eager_loading()
    {
        $user = User::create(['name' => 'user']);

        $post1 = Post::create(['title' => 'post1']);
        $post2 = Post::create(['title' => 'post2']);

        $product1 = Product::create(['name' => 'product1']);
        $product2 = Product::create(['name' => 'product2']);

        $user->bookmark($post1);
        $user->bookmark($post2);
        $user->bookmark($product1);
        $user->bookmark($product2);

        $sqls = $this->getQueryLog(function () use ($user) {
            $user->load('bookmarks.bookmarkable');
        });

        $this->assertCount(3, $sqls);

        $sqls = $this->getQueryLog(function () use ($user, $post1) {
            $user->hasBookmarked($post1);
        });

        $this->assertEmpty($sqls->all());
    }

    protected function getQueryLog(Closure $callback): Collection
    {
        $sqls = collect([]);
        DB::listen(function ($query) use ($sqls) {
            $sqls->push(['sql' => $query->sql, 'bindings' => $query->bindings]);
        });
        $callback();
        return $sqls;
    }
}
