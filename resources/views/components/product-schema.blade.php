    <script type="application/ld+json">
      {
        "@@context": "https://schema.org",
        "@@type": "Product",
        "name": {{ $title }},
        "url": {{ route('products.show', $slug) }},
        "image": {{ $image }},
        "description": {{ $description }},
        "offers": {
            "@@type": "Offer",
            "price": {{ $price }},
            "priceCurrency": "USD"
        },
        "brand": {
            "@@type": "Brand",
            "name": "Faux Vero"
        }
    }
    </script>
