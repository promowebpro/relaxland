<?php

namespace App\Domain\Blog;

enum ArticleBlockType: string
{
    case Heading = 'heading';
    case RichText = 'rich_text';
    case List = 'list';
    case Image = 'image';
    case WideImage = 'wide_image';
    case Gallery = 'gallery';
}
