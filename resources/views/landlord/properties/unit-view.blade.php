@extends('layouts.landlord.master')

@section('title')
    {{ __('Property View') }}
@endsection

@section('main_content')
    <div class="woodland-section-container">
        <div class="woodland-card">
            <div class="woodland-card-header">
                <h3>{{ __('Property View') }}</h3>
                <div class="">
                    @if ($property->status == '0')
                    <button class="pending-btn">Pending</button>
                    @elseif($property->status == '1')
                    <button class="active-btn">Active</button>
                    @elseif($property->status == '2')
                    <button class="inactive-btn">Inactive</button>
                    @else
                    <button class="rejected-btn">Rejected</button>
                    @endif
                    <a href="{{ route('landlord.properties.index') }}" class="text-black"><i class="far fa-times fs-5"></i></a>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">

                    <div class="mt-2">

                        <div class="mt-2">
                           <div class="d-flex align-items-end justify-content-between">
                            <div class="">
                                <h4 class="preview-title">{{ $property->propertyDetail->property_info['property_title'] ?? '' }}</h4>
                                @if ($property->status == '0')
                                    <p class="mt-2"> {{__('Status :')}} <span class="pending_status"> {{__('Pending')}} </span></p>
                                @elseif($property->status == '1')
                                    <p class="mt-2"> {{__('Status :')}} <span class="approve_status"> {{__('Active')}} </span></p>
                                @elseif($property->status == '2')
                                    <p class="mt-2"> {{__('Status :')}} <span class="procesing_status"> {{__('Inactive')}} </span></p>
                                @else
                                    <p class="mt-2"> {{__('Status :')}} <span class="reject_status"> {{__('Rejected')}} </span></p>
                                @endif
                            </div>
                            <div class="button-group">
                                <button class="rent-btn">{{ ucfirst($property->type) }}</button>
                                <button class="flat-btn">{{ $property->category->name ?? '' }}</button>
                            </div>
                           </div>
                            <hr class="preview-hr">
                            @if (!empty($property->propertyDetail?->property_info['property_description'] ?? ''))
                                <h5 class="preview-des"> {{__('Description')}} </h5>
                                <p class="preview-pera">
                                    {{ $property->propertyDetail->property_info['property_description'] ?? '' }}
                                </p>
                            @endif
                        </div>
                         <div class="table-responsive">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td colspan='2' class="border-0 p-0 pt-3 mb-2">
                                            <div class="table-header-title mt-2 mb-2">
                                                <h4 class=""> {{__('Property Info')}} </h4>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Property ID')}} </td>
                                        <td class="border-0 wrap-table-data">: {{ $property->puid }}</td>

                                    </tr>
                                    @if (!empty($property->address_info['address'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Property Address')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->address_info['address'] ?? '' }},
                                                {{ $property->address_info['state'] ?? '' }}, {{ $property->address_info['city'] ?? '' }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="border-0 details-name"> {{__('Property Catgeory')}} </td>
                                        <td class="border-0 wrap-table-data">:
                                            {{ $property->category->name ?? '' }}
                                        </td>
                                    </tr>
                                    @if (!empty($property->rent_info['completion_year'] ?? ''))
                                        <tr>
                                            <td class="border-0 details-name"> {{__('Completion Year')}} </td>
                                            <td class="border-0 wrap-table-data">:
                                                {{ $property->rent_info['completion_year'] ?? '' }}
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="">
                        <swiper-container class="mySwiper" thumbs-swiper=".mySwiper2" space-between="10" navigation="false">
                            @foreach ($property->cover_image ?? [] as $key => $image)
                                <swiper-slide>
                                    <img src="{{ asset($image ?: 'backend/assets/img/property-img/add-photo/camera.png') }}"
                                        alt="">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>

                        <swiper-container class="mySwiper2" space-between="10" slides-per-view="5" free-mode="true"
                            watch-slides-progress="true">
                            @foreach ($property->cover_image ?? [] as $image)
                                <swiper-slide>
                                    <img src="{{ asset($image ?: 'backend/assets/img/property-img/add-photo/camera.png') }}"
                                        alt="">
                                </swiper-slide>
                            @endforeach
                        </swiper-container>
                    </div>
                </div>
            </div>

            <div class="pro-accordion mt-3" id="proAccordion">
                @foreach ($property->units as $index => $unit )
                    <div class="pro-accordion-item {{ $loop->first ? 'pro-active' : '' }}">
                        <div class="pro-accordion-header" role="button" aria-expanded="{{ $loop->first ? 'true' : 'falseg' }}" tabindex="0">
                            <div class="pro-accordion-title">Units - {{ $unit->unit_name ?? 'Unnamed' }}</div>
                            <div class="pro-accordion-status">
                                @if ($property->type == 'rent')
                                    @if ($unit->is_booked > 0)
                                        <span class="pro-status-booking">{{ __('Booked') }}</span>
                                    @else
                                        <span class="pro-status-available">{{ __('Available') }}</span>
                                    @endif
                                @endif
                                <div class="pro-accordion-icon" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <g clip-path="url(#clip0_40000020_41447)">
                                            <path d="M9.99984 18.3333C14.6022 18.3333 18.3332 14.6023 18.3332 9.99996C18.3332 5.39759 14.6022 1.66663 9.99984 1.66663C5.39746 1.66663 1.6665 5.39759 1.6665 9.99996C1.6665 14.6023 5.39746 18.3333 9.99984 18.3333Z" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M6.6665 10H13.3332" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                            <path d="M10.0049 6.67163L10.0049 13.3383" stroke="#525252" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                        </g>
                                        <defs>
                                            <clipPath id="clip0_40000020_41447">
                                                <rect width="20" height="20" fill="white"></rect>
                                            </clipPath>
                                        </defs>
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="pro-accordion-content">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="">
                                        <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            @if (!empty($unit->unit_name))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Unit Name')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->unit_name ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if (!empty($unit->floor_range))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Floor Range')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->floor_range ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if (!empty($unit->bedroom))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Bedroom')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->bedroom ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if (!empty($unit->bathroom))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Bath')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->bathroom ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if (!empty($unit->kitchen))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Kitchen')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->kitchen ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if (!empty($unit->balcony))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Balcony')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->balcony ?? '' }}</td>
                                                </tr>
                                            @endif
                                            @if (!empty($unit->floor_range))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Condition')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->condition ?? '' }}</td>
                                                </tr>
                                            @endif
                                           @if (!empty($unit->parking_lot))
                                             <tr>
                                             <td class="border-0 details-name"> {{__('Parking')}} </td>
                                             <td class="border-0 wrap-table-data">: {{ $unit->parking_lot ?? '' }}</td>
                                             </tr>
                                           @endif
                                            @if (!empty($unit->unit_size))
                                                <tr>
                                                <td class="border-0 details-name"> {{__('Unit Size')}} </td>
                                                <td class="border-0 wrap-table-data">: {{ $unit->unit_size ?? '' }}</td>
                                                </tr>
                                            @endif

                                            <!-- Rent / Sale Pricing Section -->
                                            @if ($property->type == 'rent')
                                                <tr>
                                                    <td colspan="2" class="border-0 fw-bold pt-3">{{__('Rent Pricing')}}</td>
                                                </tr>
                                                @if (!empty($unit->monthly_rent))
                                                    <tr>
                                                    <td class="border-0 details-name"> {{__('Rent Amount')}} </td>
                                                    <td class="border-0 wrap-table-data">: {{ currency_format($unit->monthly_rent ?? '') }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($unit->min_rental_value))
                                                    <tr>
                                                    <td class="border-0 details-name"> {{__('Rental Period')}} </td>
                                                    <td class="border-0 wrap-table-data">: {{ $unit->min_rental_value ?? '' }} {{ $unit->min_rental_unit ?? '' }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($unit->security_deposit))
                                                    <tr>
                                                    <td class="border-0 details-name"> {{__('Security Deposit')}} </td>
                                                    <td class="border-0 wrap-table-data">: {{ currency_format($unit->security_deposit ?? '') }}</td>
                                                    </tr>
                                                @endif
                                               @if (!empty($unit->utility_deposit))
                                                 <tr>
                                                 <td class="border-0 details-name"> {{__('Utility Deposit')}} </td>
                                                 <td class="border-0 wrap-table-data">: {{ currency_format($unit->utility_deposit ?? '') }}</td>
                                                 </tr>
                                               @endif
                                                @if (!empty($unit->late_fee))
                                                    <tr>
                                                    <td class="border-0 details-name"> {{__('Late Fee')}} </td>
                                                    <td class="border-0 wrap-table-data">: {{ currency_format($unit->late_fee ?? '') }}</td>
                                                    </tr>
                                                @endif
                                                @if (!empty($unit->late_fee_after_days))
                                                    <tr>
                                                    <td class="border-0 details-name"> {{__('Late Fee After Days')}} </td>
                                                    <td class="border-0 wrap-table-data">: {{ $unit->late_fee_after_days ?? '' }}</td>
                                                    </tr>
                                                @endif
                                            @elseif ($property->type == 'sale')
                                                <tr>
                                                    <td colspan="2" class="border-0 fw-bold pt-3">{{__('Sale Pricing')}}</td>
                                                </tr>
                                                @if (!empty($unit->sale_amount))
                                                    <tr>
                                                        <td class="border-0 details-name"> {{__('Sale Amount')}} </td>
                                                        <td class="border-0 wrap-table-data">: {{ currency_format($unit->sale_amount ?? '') }}</td>
                                                    </tr>
                                                @endif
                                            @endif
                                        </tbody>
                                        </table>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="info-container ">
                                        @if (!empty($unit->facilities) || !empty($unit->amenities))
                                            <div class="table-header-title ">
                                                <h4> {{__('Features')}} <span> {{__('(Facilities & Amenities)')}} </span>
                                                </h4>
                                            </div>
                                        @endif
                                        <div class="p-2">
                                            @if (!empty($unit->facilities))
                                                <p class="check-title "> {{__('Facilities')}} </p>
                                                <div class="facilities-container">
                                                    @foreach ($unit->facility_names as $facility)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }}
                                                                alt="">
                                                            <h6>{{ $facility }}</h6>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if (!empty($unit->amenities))
                                                <p class="check-title mt-4"> {{__('Amenities')}} </p>
                                                <div class="facilities-container">
                                                    @foreach ($unit->amenity_names as $amenity)
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img class="circle-check" src={{ asset('backend/assets/img/circle-check.svg') }}
                                                                alt="">
                                                            <h6>{{ $amenity }}</h6>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="info-container mt-3">
                                        <div class="table-header-title ">
                                            <h4>{{ __('Floor Plans') }}</h4>
                                        </div>
                                        <div class="floor-plan-container">
                                            <img class=""
                                                src={{ asset($property->propertyDetail->floorplan_image ?? 'backend/assets/img/floor.svg') }}
                                                alt="">
                                            <p class="floor-text">{{ $property->room_info['floor_rang'] ?? '' }}</p>
                                        </div>
                                    </div>
                                    <div class="info-container mt-3">
                                        <div class="table-header-title ">
                                            <h4>{{ __('Image') }}</h4>
                                        </div>
                                        <div class="unit-container">
                                            <img src="{{ asset($unit->images[0] ?? 'backend/assets/img/floor.svg') }}" alt="unit image"
                                                class="unit-thumbnail open-gallery"
                                                data-bs-toggle="modal"
                                                data-bs-target="#imageGalleryModal-{{ $index }}">
                                        </div>
                                            <!-- Modal -->
                                        <div class="modal fade" id="imageGalleryModal-{{ $index }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content gallery-modal-content">
                                                <div class="modal-body gallery-modal-body bg-transparent">
                                                    <div class="swiper gallerySwiper">
                                                    <div class="swiper-wrapper">
                                                        @if (!empty($unit->images) && is_array($unit->images))
                                                            @foreach ($unit->images as $image)
                                                                <div class="swiper-slide">
                                                                    <img src="{{ asset($image) }}" alt="Unit Image">
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="swiper-slide">
                                                                <img src="{{ asset('backend/assets/img/floor.svg') }}" alt="Default Image">
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <!-- Navigation -->
                                                    <div class="swiper-button-prev">
                                                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect width="32" height="32" rx="16" fill="white" fill-opacity="0.9"/>
                                                        <path d="M22.0001 16H10" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M16 22L10 16L16 9.99994" stroke="#666666" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </div>
                                                    <div class="swiper-button-next">
                                                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <rect width="32" height="32" rx="16" fill="white" fill-opacity="0.9"/>
                                                        <path d="M9.99992 16H22" stroke="#104DB3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M16 22L22 16L16 9.99994" stroke="#104DB3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                        </svg>
                                                    </div>

                                                    <!-- Pagination -->
                                                    <div class="swiper-pagination p-3"></div>
                                                    </div>
                                                </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="add-landlord-button-group">
                <a href="{{ route('landlord.properties.destroy', $property->id) }}" class="btn border-btn confirm-action" data-method="DELETE">{{ __('Delete') }}</a>
                <a href="{{ route('landlord.properties.edit',$property->id) }}" class="btn theme-btn submit-btn">{{ __('Edit') }}</a>
            </div>
        </div>
    </div>
@endsection

@push('script')
<script>
document.addEventListener("DOMContentLoaded", function () {
  let swiperInstances = {};

  document.querySelectorAll(".open-gallery").forEach((galleryBtn) => {
    galleryBtn.addEventListener("click", function () {
      const targetModalId = this.getAttribute("data-bs-target");
      const modal = document.querySelector(targetModalId);

      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();

      modal.addEventListener(
        "shown.bs.modal",
        function () {
          if (swiperInstances[targetModalId]) {
            swiperInstances[targetModalId].destroy(true, true);
          }

          const swiperEl = modal.querySelector(".gallerySwiper");

          swiperInstances[targetModalId] = new Swiper(swiperEl, {
            loop: false,
            slidesPerView: 1,
            centeredSlides: true,
            navigation: {
              nextEl: modal.querySelector(".swiper-button-next"),
              prevEl: modal.querySelector(".swiper-button-prev"),
            },
            pagination: {
              el: modal.querySelector(".swiper-pagination"),
              type: "fraction",
              renderFraction: (currentClass, totalClass) =>
                `<span class="${currentClass}"></span> of <span class="${totalClass}"></span>`,
            },
            spaceBetween: 10,
            observer: true,
            observeParents: true,
          });

          // Force reflow
          setTimeout(() => swiperInstances[targetModalId].update(), 300);
        },
        { once: true }
      );
    });
  });
});
</script>

<script src="{{ asset('backend/assets/js/swiper-bundle.min2.js') }}"></script>

@endpush


