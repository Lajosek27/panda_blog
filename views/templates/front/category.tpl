{extends file='page.tpl'}

 

{block name='page_content_container'}

 
   
<section id="pandawpblog--category">
     <div class="container">
        <div class="text-wrapper">
            <h2>{$category.name}</h2>
            <hr>
      
        </div>
        <div class="row">
            {foreach from=$posts item=post}
                <div class=" col-md-6 col-lg-3 mb-4">
                    <div class="card ">
                        <div class="wrapper-img">
                            {if $post.image}
                                <img src="{$post.image}" class="card-img-top" alt="{$post.title}">
                            {/if}

                            <p>{$post.main_category.name}</p>
                        </div>

                        <div class="card-body">
                            <div class="row-details">
                                <div class="date">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M14.1147 4.54419C14.4671 4.19181 14.6652 3.71384 14.6652 3.21543C14.6653 2.71702 14.4674 2.239 14.115 1.88652C13.7626 1.53405 13.2846 1.336 12.7862 1.33594C12.2878 1.33587 11.8098 1.53381 11.4573 1.88619L2.55999 10.7855C2.4052 10.9399 2.29073 11.1299 2.22665 11.3389L1.34599 14.2402C1.32876 14.2978 1.32746 14.3591 1.34222 14.4174C1.35699 14.4758 1.38727 14.529 1.42985 14.5715C1.47244 14.614 1.52573 14.6442 1.58409 14.6589C1.64245 14.6736 1.70369 14.6722 1.76132 14.6549L4.66332 13.7749C4.8721 13.7114 5.0621 13.5976 5.21665 13.4435L14.1147 4.54419Z"
                                            stroke="#121231" stroke-linecap="round" stroke-linejoin="round" />
                                        <path d="M10 3.33594L12.6667 6.0026" stroke="#121231" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                    <p>{$post.date_add|date_format:"%d.%m.%Y"}</p>
                                </div>
                                <div class="autor">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M8.0013 8.66667C9.84225 8.66667 11.3346 7.17428 11.3346 5.33333C11.3346 3.49238 9.84225 2 8.0013 2C6.16035 2 4.66797 3.49238 4.66797 5.33333C4.66797 7.17428 6.16035 8.66667 8.0013 8.66667Z"
                                            stroke="#121231" stroke-linecap="round" stroke-linejoin="round" />
                                        <path
                                            d="M13.3346 13.9974C13.3346 12.5829 12.7727 11.2264 11.7725 10.2262C10.7723 9.22597 9.41579 8.66406 8.0013 8.66406C6.58681 8.66406 5.23026 9.22597 4.23007 10.2262C3.22987 11.2264 2.66797 12.5829 2.66797 13.9974"
                                            stroke="#121231" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <p>{$post.author}</p>
                                </div>
                            </div>
                            <h3 class="card-title">{$post.title}</h3>
                               <p class="card-text">{$post.excerpt|strip_tags}</p>
                            <a href="{$post.url}" class="read-more">{l s='Czytaj więcej' d='Shop.Theme.Global'}
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M7.82322 5.64404C7.88459 5.6441 7.92782 5.66075 7.97752 5.71045L12.0967 9.83057C12.1355 9.86945 12.1504 9.89513 12.1553 9.90674V9.90771C12.1631 9.9267 12.1679 9.94912 12.1679 9.979C12.1679 10.009 12.1631 10.0313 12.1553 10.0503C12.1504 10.0619 12.1356 10.0885 12.0967 10.1274L7.95604 14.2681C7.90671 14.3174 7.8736 14.3262 7.83104 14.3247C7.77477 14.3226 7.72661 14.3052 7.66893 14.2476C7.61913 14.1978 7.60252 14.1548 7.60252 14.0933C7.60255 14.0318 7.61918 13.9887 7.66893 13.939L11.6289 9.979L7.64744 5.99756C7.59839 5.94843 7.59029 5.91498 7.59178 5.87256C7.59384 5.81629 7.61127 5.76813 7.66893 5.71045C7.71872 5.66066 7.76168 5.64404 7.82322 5.64404Z"
                                        fill="#0084FF" stroke="#0084FF" />
                                </svg>

                            </a>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
</section>



{/block}