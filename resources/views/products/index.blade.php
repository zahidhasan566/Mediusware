@extends('layouts.app')

@section('content')

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Products</h1>
    </div>

    <div class="card">
        <form method="GET" {{ action('ProductController@index') }} class="card-header">
            <div class="form-row justify-content-between">
                <div class="col-md-2">
                    <input type="text" name="title" placeholder="Product Title" class="form-control">
                </div>
                <div class="col-md-2">
                    <select name="variant" id="" class="form-control">
                        <option value="">Select a variant</option>
                        @foreach($product_variants as $product_variant)
                            <option value="{{ $product_variant->variant_id }}">{{ $product_variant->variant }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">Price Range</span>
                        </div>
                        <input type="text" name="price_from" aria-label="First name" placeholder="From"
                               class="form-control">
                        <input type="text" name="price_to" aria-label="Last name" placeholder="To" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date" placeholder="Date" class="form-control">
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
                        <th>price</th>
                        <th>Stock</th>
                        <th width="150px">Action</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($all_products as $single_product)

                        <tr>
                            <td>{{$single_product->id}}</td>
                            <td>{{$single_product->title}}</td>
                            <td>{{$single_product->description}}</td>
                            <td>
                                @if(isset($single_product->productVariants) && count($single_product->productVariants) > 0)
                                    @foreach($single_product->productVariants as $single_product_variant)
                                        {{$single_product_variant->variant}},
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                @if(isset($single_product->productVariantPrices))
                                    {{ number_format($single_product->productVariantPrices->price,2)}}

                                @endif
                            </td>
                            <td>
                                @if(isset($single_product->productVariantPrices))
                                    {{ number_format($single_product->productVariantPrices->stock,2)}}

                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('product.edit', $single_product->id) }}" class="btn btn-success">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    </tbody>

                </table>
            </div>

        </div>

        <div class="card-footer">
            <div class="row justify-content-between">
                <div class="col-md-6">
                    {{$all_products->appends(Request::except('page'))->links() }}
                    <p>Showing {{$all_products->count()}} out of {{ $all_products->total() }}</p>
                </div>
                <div class="col-md-2">

                </div>
            </div>
        </div>
    </div>

@endsection
