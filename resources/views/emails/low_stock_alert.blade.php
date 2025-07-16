Low Stock Alert:

@foreach ($products as $product)
    <p>{{ $product->name }} is low on stock ({{ $product->stock_quantity }} left).</p>
@endforeach