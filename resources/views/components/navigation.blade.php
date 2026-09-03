<nav>
    <ul>
        <li><a href="{{ route('homepage') }}">Home</a></li>
        <li><a href="{{ route('products.index') }}">Products</a></li>
        <li><a href="{{ route('page.show', ['slug' => 'about']) }}">About</a></li>
        <li><a href="{{ route('page.show', ['slug' => 'contact']) }}">Contact</a></li>
    </ul>
</nav>