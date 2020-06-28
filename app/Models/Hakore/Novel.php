<?php

namespace App\Models\Hakore;

use Illuminate\Database\Eloquent\Model;
use App\Http\Traits\DomHandler;

class Novel extends Model
{
    use DomHandler;
    
    private $dom;

    protected $fillable = [
        'content'
    ];

    public function __construct(array $attributes = array()){
        parent::__construct($attributes);

        $this->dom = new \DomDocument();
        $this->dom->loadHtml($this->content);
    }
}
