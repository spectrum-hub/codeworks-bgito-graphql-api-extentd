<?php

namespace Webkul\GraphQLAPI\Models\Blog;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Webbycrown\BlogBagisto\Contracts\Blog as BlogContract;
use Webkul\Core\Models\ChannelProxy;
use Illuminate\Support\Facades\Storage;
use Webbycrown\BlogBagisto\Models\Category;
use Webkul\Core\Eloquent\TranslatableModel;


class Blog extends TranslatableModel implements BlogContract
{
    use HasFactory;

    protected $table = 'blogs';

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'channels',
        'default_category',
        'author',
        'author_id',
        'categorys',
        'tags',
        'src',
        'status',
        'locale',
        'allow_comments',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at'
    ];



    protected $translationForeignKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */


    /**
     * The attributes that are translatable.
     *
     * @var array
     */
    public $translatedAttributes = [
        'short_description',
        'description',
        'meta_description',
        'meta_title',
        'page_title',
        'meta_keywords',
        'html_content',
        'url_key',
        'name',
    ];

    protected $with = ['translations'];


    /**
     * Appends.
     *
     * @var array
     */
    protected $appends = ['src_url', 'assign_categorys'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'default_category');
    }

    /**
     * Get the channels.
     */
    public function channels()
    {
        return $this->belongsToMany(ChannelProxy::modelClass(), 'channels');
    }

    /**
     * Get image url for the category image.
     *
     * @return string
     */
    public function getSrcUrlAttribute()
    {
        if (! $this->src) {
            return;
        }

        return Storage::url($this->src);
    }

    public function getAssignCategorysAttribute()
    {
        $categorys = [];
        $categories_ids = array_values( array_unique( array_merge( explode( ',', $this->default_category ), explode( ',', $this->categorys ) ) ) );
        if ( is_array($categories_ids) && !empty($categories_ids) && count($categories_ids) > 0 ) {
            $categories = Category::whereIn('id', $categories_ids)->get();
            $categorys = ( !empty($categories) && count($categories) > 0 ) ? $categories : array();
        }
        return $categorys;
    }
    
    








}