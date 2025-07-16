<x-mail::message>
# New Supply Request

Dear {{ $supply->vendorCompany->name ?? 'Vendor' }},

You have received a new supply request. Here are the details:

- **Reference Number:** {{ $supply->reference_number }}
- **Date:** {{ $supply->date->format('Y-m-d') }}
- **Requested By:** {{ $supply->creator->name ?? 'N/A' }}

---

### Items Requested

| Product              | Quantity   | Unit Price    | Total Price   |
|:---------------------|:-----------|:--------------|:--------------|
@foreach($supply->items as $item)
| {{ $item->product->name }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 2) }} | {{ number_format($item->total_price, 2) }} |
@endforeach

---

<x-mail::panel>
<!-- **Tariff Fee:** {{ number_format($supply->tariff_fee, 2) }}  
**Import Cost:** {{ number_format($supply->import_cost, 2) }}   -->
**Total:** {{ number_format($supply->items->sum(fn($i)=>$i->total_price) + $supply->tariff_fee + $supply->import_cost, 2) }}
</x-mail::panel>

<!-- <x-mail::button :url="url('/login')">
Review & Confirm Supply
</x-mail::button> -->

Thanks,

Inventory System
</x-mail::message>
