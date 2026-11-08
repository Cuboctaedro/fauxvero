<button
    type="button"
    data-disclosure
    aria-expanded="false"
    aria-controls="language-menu"
    class="w-8 h-8 flex items-center justify-center focus:outline-2 focus:outline-offset-1 focus:outline-gray-300 cursor-pointer"
>
    <span class="block w-6 h-6" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="size-6" viewBox="0 0 24 24">
            <path d="M21.721 12.752a9.7 9.7 0 0 0-.945-5.003 12.8 12.8 0 0 1-4.339 2.708 19 19 0 0 1-.214 4.772 17.2 17.2 0 0 0 5.498-2.477m-7.087 2.798a17.3 17.3 0 0 0 .332-4.647c-.952.227-1.945.347-2.966.347s-2.014-.12-2.966-.347a17.5 17.5 0 0 0 .332 4.647 17.4 17.4 0 0 0 5.268 0m-4.862 1.569a19 19 0 0 0 4.456 0A17.2 17.2 0 0 1 12 21.724a17.2 17.2 0 0 1-2.228-4.605M7.777 15.23a18.9 18.9 0 0 1-.214-4.774 12.8 12.8 0 0 1-4.34-2.708 9.7 9.7 0 0 0-.944 5.004 17.2 17.2 0 0 0 5.498 2.477Zm13.579-.478a9.77 9.77 0 0 1-7.478 6.817 18.6 18.6 0 0 0 1.988-4.718 18.6 18.6 0 0 0 5.49-2.098Zm-18.712 0a18.6 18.6 0 0 0 5.49 2.099 18.6 18.6 0 0 0 1.988 4.718 9.77 9.77 0 0 1-7.478-6.816ZM13.878 2.43a9.76 9.76 0 0 1 6.116 3.986 11.3 11.3 0 0 1-3.746 2.504 18.6 18.6 0 0 0-2.37-6.49M12 2.276a17.15 17.15 0 0 1 2.805 7.121 11.3 11.3 0 0 1-5.61 0A17.15 17.15 0 0 1 12 2.276m-1.878.154a18.6 18.6 0 0 0-2.37 6.49 11.3 11.3 0 0 1-3.746-2.504 9.75 9.75 0 0 1 6.116-3.985Z" />
        </svg>
    </span>
    <span class="sr-only">
        {{ __('Language') }}: {{ config('app.locales')[app()->getLocale()] ?? app()->getLocale() }}
    </span>
</button>

<ul
    id="language-menu"
    class="hidden absolute right-0 top-full mt-2 min-w-40 flex flex-col gap-3 p-4 uppercase tracking-wider bg-white dark:bg-gray-900 shadow-lg z-50"
>
    @foreach (config('app.locales') as $code => $name)
        <li>
            @if ($code === app()->getLocale())
                <span aria-current="true" lang="{{ $code }}" class="pb-1 border-b-2 border-current">{{ $name }}</span>
            @else
                <a
                    href="{{ \App\Support\LocalizedUrl::current($code) }}"
                    hreflang="{{ $code }}"
                    lang="{{ $code }}"
                    class="pb-1 border-b-2 border-transparent hover:border-gray-800 text-gray-600 dark:text-gray-400"
                >
                    {{ $name }}
                </a>
            @endif
        </li>
    @endforeach
</ul>
