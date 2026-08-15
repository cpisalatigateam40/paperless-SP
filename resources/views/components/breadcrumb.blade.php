@props(['items' => []])

<nav aria-label="breadcrumb" class="rm-breadcrumb mb-3">
    <ol>
        @foreach ($items as $item)
            @if (!$loop->last && !empty($item['url']))
                <li>
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                </li>
            @else
                <li class="active" aria-current="page">
                    {{ $item['label'] }}
                </li>
            @endif

            @if (!$loop->last)
                <li class="separator">&gt;</li>
            @endif
        @endforeach
    </ol>
</nav>

<style>
    .rm-breadcrumb {
        border: .2px solid #cc706452;
        display: inline-block;
        background-color: #ffffff;
        padding: 8px 16px;
        border-radius: 8px;
        box-shadow: 0 .15rem 1.75rem 0 rgba(58, 59, 69, .15) !important;
    }

    .rm-breadcrumb ol {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .rm-breadcrumb li {
        font-size: 14px;
        color: #C9822B;
    }

    .rm-breadcrumb li a {
        color: #C9822B;
        text-decoration: underline;
    }

    .rm-breadcrumb li a:hover {
        color: #A8681C;
    }

    .rm-breadcrumb li.active {
        color: #4A3418;
        font-weight: 700;
    }

    .rm-breadcrumb li.separator {
        margin: 0 10px;
        color: #C9822B;
        font-weight: 400;
    }
</style>