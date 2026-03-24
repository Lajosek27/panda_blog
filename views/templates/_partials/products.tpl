{extends file="components/featured-products.tpl"}

{block name='featured_products'}
  <div class="featured-products my-4 promo-block {block name='featured_products_class'}{/block}">

    {block name='featured_products_header'}
      <div class="featured-products__header d-flex align-items-center mb-3">
        {block name='featured_products_title'}
          <h2 class="h1 featured-products__title m-0">
            {l s='Powiązane produkty' d='Shop.Theme.Catalog'}
            <hr>
          </h2>
        {/block}
      </div>

      <div class="featured-products__navigation ">
        <div class="swiper-button-prev swiper-button-custom ">
          <span class="sr-only">{l s='Previous' d='Shop.Theme.Actions'}</span>
          <svg width="19" height="35" viewBox="0 0 19 35" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M17.7071 34.5L0.707094 17.5L17.7071 0.5" stroke="black" stroke-linecap="round" />
          </svg>

        </div>
        <div class="swiper-button-next swiper-button-custom ">
          <span class="sr-only">{l s='Next' d='Shop.Theme.Actions'}</span>
          <svg width="19" height="35" viewBox="0 0 19 35" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0.5 0.5L17.5 17.5L0.5 34.5" stroke="black" stroke-linecap="round" />
          </svg>

        </div>
      </div>
    {/block}

    {$sliderConfig = [
              'speed' => 500,
              'breakpoints' => [
                '320' => [
                  'slidesPerView' => 2
                ],
                '768' => [
                  'slidesPerView' => 3
                ],
                '992' => [
                  'slidesPerView' => 4
                ],
                '1280' => [
                  'slidesPerView' => 5
                ]
              ]
            ]}

    <div class="swiper product-slider py-1 my-n1"
      data-swiper='{block name="featured_products_slider_options"}{$sliderConfig|json_encode}{/block}'>
      {block name='featured_products_products'}
        <div class="featured-products__slider swiper-wrapper {block name='featured_products_slider_class'}{/block}">
          {foreach from=$products item="product"}
            {block name='product_miniature'}
              {include file='catalog/_partials/miniatures/product.tpl' product=$product type='slider'}
            {/block}
          {/foreach}
        </div>
      {/block}
    </div>


  </div>
{/block}