Low Stock Alert:

@foreach ($products as $product)
    <p>{{ $product->name }} is low on stock ({{ $product->quantity }} left).</p>
@endforeach

