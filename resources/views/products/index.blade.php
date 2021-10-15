@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>


    <div class="card">
        <form action="{{ route('product.index') }}" method="get" class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" value="{{ $request->title }}"
                           placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        @foreach($variants as $key => $product_variants)
                            <optgroup label="{{ $key }}">
                                @foreach($product_variants as $product_variant)
                                    <option
                                        value="{{ $product_variant->id }}" {{ $request->variant == $product_variant->id ? 'selected' : '' }}>
                                        {{ $product_variant->variant }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                               class="form-control" value="{{ $request->price_from }}">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control"
                               value="{{ $request->price_to }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date"
                           value="{{ $request->date ? date('Y-m-d', strtotime($request->date)) : '' }}"
                           placeholder="Date" class="form-control">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary float-right"><i class="fa fa-search"></i></button>
                </div>
            </div>
        </form>

        <div class="card-body">
            <div class="table-response">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Variant</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>

                    @forelse($products as $productKey => $product)
                        {{--                        @dd($product->product_variants->groupBy('product_id'))--}}
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                {{ $product->title }}
                                <br> Created at : {{ $product->created_at->diffForHumans() }}
                            </td>
                            <td>{{ $product->description }}</td>
                            <td>
                                <dl class="row mb-0" style="height: 80px; overflow: hidden"
                                    id="variant{{ $productKey }}">

                                    @forelse($product->product_variant_prices as $product_variant_price)
                                        <dt class="col-sm-3 pb-0">
                                            {{
    $product_variant_price->product_variant_one_text . '/' .  $product_variant_price->product_variant_two_text . '/' . $product_variant_price->product_variant_three_text
    }}
                                        </dt>
                                        <dd class="col-sm-9">
                                            <dl class="row mb-0">
                                                <dt class="col-sm-4 pb-0">
                                                    Price : {{ number_format($product_variant_price->price,2) }}
                                                </dt>
                                                <dd class="col-sm-8 pb-0">InStock
                                                    : {{ number_format($product_variant_price->stock,2) }}</dd>
                                            </dl>
                                        </dd>
                                    @empty
                                        <span>No product prices found!</span>
                                    @endforelse
                                </dl>
                                <button onclick="$('#variant{{ $productKey }}').toggleClass('h-auto')"
                                        class="btn btn-sm btn-link">
                                    Show more
                                </button>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', $product->id) }}" class="btn btn-success">
                                        Edit
                                    </a>
                                    <button class="btn btn-danger"
                                            onclick="event.preventDefault();$('#deleteForm{{ $product->id }}').submit();">
                                        Delete
                                    </button>
                                    <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                          id="deleteForm{{ $product->id }}">
                                        @csrf
                                        @method("DELETE")
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-danger text-center" colspan="100%">
                                No data found!
                            </td>
                        </tr>
                    @endforelse

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    <p>
                        Showing
                        {{ (($products->currentpage() - 1) * $products->perpage()) + 1 }}
                        to
                        {{ $products->currentpage() * $products->perpage() }} out
                        of
                        {{ $products->total() }}
                    </p>
                </div>
                <div class="col-md-4">
                    {{ $products->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>

@endsection
