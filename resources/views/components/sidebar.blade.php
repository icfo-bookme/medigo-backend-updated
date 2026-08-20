<style>
    .highlight {
    background-color: rgb(197, 23, 75); /* Or any color you prefer */
    font-weight: bold; /* Optional to make the matched word bold */
}

</style>
<div class="aside aside-left  aside-fixed  d-flex flex-column flex-row-auto" id="kt_aside">
    <div class="brand flex-column-auto " id="kt_brand" style="padding: 0 20px !important;">
        <div class="brand-logo" style="background: white;
        padding: 5px;
        box-shadow: 0 0px 0px 5px rgba(255, 255, 255, 0.5);">
            @if (config('settings.logo'))
            <a href="{{  url('/')  }}"><img src="{{ asset('storage/'.LOGO_PATH.config('settings.logo'))}}" style="max-width: 170px;" alt="Logo" /></a>
            @else
            <h3 class="text-white">{{ config('settings.title') ? config('settings.title') : env('APP_NAME') }}</h3>
            @endif
        </div>
        <button class="brand-toggle btn btn-sm px-0" id="kt_aside_toggle">
            <span class="svg-icon svg-icon-xl">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                    <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                        <polygon points="0 0 24 0 24 24 0 24" />
                        <path d="M5.29288961,6.70710318 C4.90236532,6.31657888 4.90236532,5.68341391 5.29288961,5.29288961 C5.68341391,4.90236532 6.31657888,4.90236532 6.70710318,5.29288961 L12.7071032,11.2928896 C13.0856821,11.6714686 13.0989277,12.281055 12.7371505,12.675721 L7.23715054,18.675721 C6.86395813,19.08284 6.23139076,19.1103429 5.82427177,18.7371505 C5.41715278,18.3639581 5.38964985,17.7313908 5.76284226,17.3242718 L10.6158586,12.0300721 L5.29288961,6.70710318 Z" fill="#000000" fill-rule="nonzero" transform="translate(8.999997, 11.999999) scale(-1, 1) translate(-8.999997, -11.999999) " />
                        <path d="M10.7071009,15.7071068 C10.3165766,16.0976311 9.68341162,16.0976311 9.29288733,15.7071068 C8.90236304,15.3165825 8.90236304,14.6834175 9.29288733,14.2928932 L15.2928873,8.29289322 C15.6714663,7.91431428 16.2810527,7.90106866 16.6757187,8.26284586 L22.6757187,13.7628459 C23.0828377,14.1360383 23.1103407,14.7686056 22.7371482,15.1757246 C22.3639558,15.5828436 21.7313885,15.6103465 21.3242695,15.2371541 L16.0300699,10.3841378 L10.7071009,15.7071068 Z" fill="#000000" fill-rule="nonzero" opacity="0.3" transform="translate(15.999997, 11.999999) scale(-1, 1) rotate(-270.000000) translate(-15.999997, -11.999999) " />
                    </g>
                </svg>
            </span>
        </button>
    </div>



    <div class="aside-menu-wrapper flex-column-fluid" id="kt_aside_menu_wrapper">
        <div id="kt_aside_menu" class="aside-menu my-4 " data-menu-vertical="1" data-menu-scroll="1" data-menu-dropdown-timeout="500">

            <input type="text" id="searchInput" class="form-control" placeholder="Search Console" oninput="find_menu(this)">

            <ul class="menu-nav ">
                @if(Session::get('user_menu'))
                @foreach (Session::get('user_menu') as $menu)


                    @if($menu->children->isEmpty())
                        @if ($menu->type == 1)
                            <li class="menu-section "><h4 class="menu-text">{{ $menu->divider_title }}</h4></li>
                        @else
                            <li class="menu-item  {{ (request()->is($menu->url)) ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                <a href="{{ $menu->url ? url($menu->url) : '' }}" class="menu-link" target="{{ $menu->target ?? '_self' }}">
                                    <span class="svg-icon menu-icon"><i class="{{ $menu->icon_class }}"></i></span>
                                    <span class="menu-text">{{ $menu->module_name }}</span>
                                </a>
                            </li>
                        @endif
                    @else

                        <li class="menu-item  menu-item-submenu

                        @foreach ($menu->children as $submenu)
                            {{ (request()->is($submenu->url)) ? 'menu-item-open' : '' }}
                            @if(!$submenu->children->isEmpty())
                                @foreach ($submenu->children as $sub_submenu)
                                {{ (request()->is($sub_submenu->url)) ? 'menu-item-open' : '' }}
                                @endforeach
                            @endif
                        @endforeach


                        " aria-haspopup="true" data-menu-toggle="hover">
                            <a href="javascript:void();" class="menu-link menu-toggle">
                                <span class="svg-icon menu-icon"><i class="{{ $menu->icon_class }}"></i></span>
                                <span class="menu-text">{{ $menu->module_name }}
                                    @if($menu->module_name === "Order")

                                        <span class="badge badge-pill badge-success mx-auto new_sale_counter">0</span>
                                    @endif
                                </span>
                                <i class="menu-arrow"></i>
                            </a>
                            <div class="menu-submenu ">
                                <span class="menu-arrow"></span>
                                <ul class="menu-subnav">
                                    @foreach ($menu->children as $submenu)

                                        @if($submenu->children->isEmpty())
                                            <li class="menu-item {{ (request()->is($submenu->url)) ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                                <a href="{{ $submenu->url ? url($submenu->url) : '' }}" class="menu-link ">
                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                    <span class="menu-text">{{ $submenu->module_name }}</span>
                                                </a>
                                            </li>
                                        @endif

                                        @if($submenu->children->isNotEmpty())
                                            <li class="menu-item  menu-item-submenu
                                                @foreach ($submenu->children as $sub_submenu)
                                                {{ (request()->is($sub_submenu->url)) ? 'menu-item-open' : '' }}
                                                @endforeach
                                                " aria-haspopup="true" data-menu-toggle="hover">
                                                <a href="javascript:void();" class="menu-link menu-toggle">
                                                    <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                    <span class="menu-text">{{ $submenu->module_name }}</span>
                                                    <i class="menu-arrow"></i>
                                                </a>
                                                <div class="menu-submenu ">
                                                    <span class="menu-arrow"></span>
                                                    <ul class="menu-subnav">
                                                        @foreach ($submenu->children as $sub_submenu)
                                                        <li class="menu-item {{ (request()->is($sub_submenu->url)) ? 'menu-item-active' : '' }}" aria-haspopup="true">
                                                            <a href="{{ $sub_submenu->url ? url($sub_submenu->url) : '' }}" class="menu-link ">
                                                                <i class="menu-bullet menu-bullet-dot"><span></span></i>
                                                                <span class="menu-text">{{ $sub_submenu->module_name }}</span>
                                                            </a>
                                                        </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </li>
                                        @endif


                                    @endforeach
                                </ul>
                            </div>
                        </li>
                    @endif
                @endforeach
                @endif
            </ul>
        </div>
    </div>
</div>
<script>

function find_menu(on_this) {
    let element = $(".menu-submenu");
    let inputValue = on_this.value.trim().toLowerCase();

    if (!inputValue) {
        element.css({
            'display': 'none',
            'overflow': 'hidden'
        });

        $(".menu-item").each(function() {
            $(this).show();
            $(this).find(".menu-text").each(function() {
                $(this).html($(this).text()); // Remove any previous highlighting
            });
        });
        return;
    }

    element.css({
        'display': 'flex',
        'flex-grow': '1',
        'flex-direction': 'column'
    });

    $(".menu-item").each(function() {
        var menuItem = $(this);
        var isMatch = false;

        menuItem.find(".menu-text").each(function() {
            let elementText = $(this).text().trim();
            let elementTextLower = elementText.toLowerCase();

            // Check if the input value is found within the element text
            if (elementTextLower.includes(inputValue)) {
                isMatch = true;

                // Highlight the matched word by wrapping it in a span with a specific color
                let regex = new RegExp(`(${inputValue})`, 'gi');
                let highlightedText = elementText.replace(regex, '<span class="highlight">$1</span>');
                
                $(this).html(highlightedText); // Update the HTML with the highlighted word
            } else {
                // Reset to original text (remove any previous highlighting)
                $(this).html(elementText);
            }
        });

        if (isMatch) {
            menuItem.show();
            $(this).closest(".menu-submenu").show();
        } else {
            menuItem.hide();
        }
    });
}




</script>
