@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/property-search.css') }}">

<div class="property-search-page">

    <!-- HEADER -->
    <div class="property-page-header">
        <h1>Search Properties</h1>
        <p>Find properties based on client preferences.</p>
    </div>

    <!-- SEARCH CARD -->
    <div class="property-search-card">

        <!-- CLIENT -->
        <div class="property-search-group property-full">
            <label>Select Client</label>

            <select id="renterSelect">
                <option>Select Client</option>

                @foreach($renters as $renter)
                    <option value="{{ $renter->renter_no }}">
                        {{ $renter->fname }} {{ $renter->lname }}
                    </option>
                @endforeach

            </select>
        </div>

        <!-- BUTTON -->
        <div class="property-search-button-wrap">
            <button class="property-search-btn">
                Search
            </button>
        </div>

        <!-- PREFERENCES -->
        <h2 class="property-section-title">
            Client Preferences
        </h2>

        <!-- GRID -->
        <div class="property-search-grid">

            <div class="property-search-group">
                <label>Preferred Type</label>
                <input type="text">
            </div>

            <div class="property-search-group">
                <label>Preferred Location</label>
                <input type="text">
            </div>

            <div class="property-search-group property-full">
                <label>Max Rent</label>
                <input type="number">
            </div>

        </div>

        <!-- RESULTS -->
        <div class="property-results-card">

            <table class="property-results-table">

                <thead>
                    <tr>
                        <th>Property No</th>
                        <th>Street</th>
                        <th>Area</th>
                        <th>City</th>
                        <th>Property Type</th>
                        <th>Rooms</th>
                        <th>Monthly Rent</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($properties as $property)

                    <tr>
                        <td>{{ $property->property_no }}</td>
                        <td>{{ $property->street }}</td>
                        <td>{{ $property->area }}</td>
                        <td>{{ $property->city }}</td>
                        <td>{{ $property->property_type }}</td>
                        <td>{{ $property->rooms }}</td>
                        <td>{{ $property->monthly_rent }}</td>
                    </tr>

                    @empty

                    <tr>
                        <td colspan="7">
                            No properties found.
                        </td>
                    </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection