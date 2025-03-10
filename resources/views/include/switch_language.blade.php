
                    <div class="row">
                        <div class="col-12">
                            <div class="dropdown mt-2">
                                <a href="#" role="button" id="dropdownLanguage" class="px-3 dropdown-toggle hidden-arrow text-dark" data-mdb-toggle="dropdown" aria-expanded="false" title="@lang('miscellaneous.your_language')">
                                    <i class="bi bi-translate fs-4"></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="dropdownLanguage">
        @foreach ($available_locales as $locale_name => $available_locale)
            @if ($available_locale != $current_locale)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('change_language', ['locale' => $available_locale]) }}">
                @switch($available_locale)
                    @case('ln')
                                            <span class="fi fi-cd me-2"></span>
                        @break
                    @case('en')
                                            <span class="fi fi-us me-2"></span>
                        @break
                    @default
                                            <span class="fi fi-{{ $available_locale }} me-2"></span>
                @endswitch
                                            {{ $locale_name }}
                                        </a>
                                    </li>
            @endif
        @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

