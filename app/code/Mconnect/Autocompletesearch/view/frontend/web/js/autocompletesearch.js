
if (!window.hasOwnProperty('MCS')) {MCS = {};}

	MCS.AutoCompleteSearch = {
		
		ajaxurl: "",
		getViewAllLinkStatus:"",
		chLen:"",	
		getViewAllLinkText:"",
		getCountSearchResults:"",
		
		init: function ($) {
			
			jQuery(document).on('keyup','#search, #mobile_search, .minisearch input[type="text"]', function (event) {
				jQuery('#search, #search_autocomplete, #mobile_search, .minisearch input[type="text"]')
                .unbind('input') ;
			});
						
			
			var xhr = null; 
			
			jQuery("input#search").autocomplete({	
					source: function (request, response) {			
						
					if( xhr != null ) {
						xhr.abort();
						xhr = null;
					}
							
						var q=request.term;
						
						xhr = jQuery.ajax({
								url: MCS.AutoCompleteSearch.ajaxurl,
								 data: { q: q },			
								type:'GET',
								dataType: 'json',
								beforeSend: function(){	
								
								jQuery('.actions .search').addClass('search-loadin-image');
									
									var response = localStorage.getItem(q);
									if(response!=null){
											
										response = JSON.parse(response);						
										//console.log(response);
										
										var result=MCS.AutoCompleteSearch.filterData(response);		
									
									htmlObject=jQuery("#custom_search_autocomplete").html(result);	
									
									htmlObject.find('[data-role=autocompletesearch-tocart-form], .form.map.checkout')
							.attr('data-mage-init', JSON.stringify({'catalogAddToCart': {}}));				
							htmlObject.trigger('contentUpdated');
							
									jQuery("#custom_search_autocomplete").show();
									jQuery('.actions .search').removeClass('search-loadin-image');
									
									}
								
								},
								success:function(response){
									
									
									localStorage.removeItem(q);
									localStorage.setItem(q, JSON.stringify(response));
									
									
									var result=MCS.AutoCompleteSearch.filterData(response);						
									
									htmlObject=jQuery("#custom_search_autocomplete").html(result);						
							
							htmlObject.find('[data-role=autocompletesearch-tocart-form], .form.map.checkout')
							.attr('data-mage-init', JSON.stringify({'catalogAddToCart': {}}));				
							htmlObject.trigger('contentUpdated');						
							
									jQuery("#custom_search_autocomplete").show();
									jQuery('.actions .search').removeClass('search-loadin-image');
									
									
								}
						});
						
					},
					minLength: MCS.AutoCompleteSearch.chLen
				});
		},
		
		filterData : function (response){		
		console.log(response);		
		var dt='';
			
			dt += '<div class="mcs-autosearch-main">';	
		/*	
		if(response.category ||  response.pages){	
			
			dt += '<div class="mcs-autosearch-left">';
			if(response.category){
			var categoryResult=MCS.AutoCompleteSearch.categoryData(response);
				dt += categoryResult;
			}
			
			if(response.pages){
				
			var pagesResult=MCS.AutoCompleteSearch.pagesData(response);
				dt += pagesResult;
			}
			
			dt += '</div>';
			
			dt += '<div class="mcs-autosearch-right">';
		}else{
		
			dt += '<div class="mcs-autosearch-full">';
		}
		*/	
		
		if(response.category ||  response.pages){
			dt += '<div class="mcs-autosearch-right">';
		}else{
			dt += '<div class="mcs-autosearch-full">';
		}
		
			dt += '<ul>';	
			if(response.products.data){
			jQuery.each(response.products.data, function(index, element) {
				
				dt += '<li class="search-li">';
				
				if(element.image){
				 dt +='<div class="sr-li-left">';
				 dt +='<a href="'+element.url+'"><img style="display:block" width="70px" src="'+element.image+'"></a>';					      
				 dt +='</div>';
				 }
				 
				 dt +='<div class="sr-li-right">';
					
					if(element.name)
					dt +='<a href="'+element.url+'">'+element.name+'</a>'; 	
				
					if(element.sku)
					dt +='<br><span>SKU : '+element.sku+'</span>';
					
					if(element.reviews_rating)
					dt +=element.reviews_rating;
					
					if(element.description)
					dt +='<p class="mcs-description">'+element.description+'</p>';
					
					if(element.price)
					dt +=element.price;
				
					if(element.add_to_cart){
						
						var form='';
						form +='<div class="autocompletesearch-form">';
						form +='<form data-role="autocompletesearch-tocart-form" action="'+element.add_to_cart.formUrl+'" method="post" >';
							
							form +='<input type="hidden" name="product" value="'+element.add_to_cart.productId+'">';
							form +='<input type="hidden" name="uenc" value="'+element.add_to_cart.uenc+'">';
							form +='<input type="hidden" name="form_key" value="'+element.add_to_cart.formKey+'">';									
							form +='<button type="submit" title="Add to Cart" class="action tocart primary">';
								form +='<span> Add to Cart </span>';
							form +='</button>';
						
						form +='</form>';
						form +='</div>';
					
					dt +=form;		
					}
					//console.log(form);
					
					
				 dt +='</div>';					
				
				dt +='<div class="clear"></div>';		 
				 
				dt += '</li>';	

			});
			}
			if(response.products.size > 0){
				
				if(MCS.AutoCompleteSearch.getViewAllLinkStatus==1){
					
					dt +='<li class="mcs-view-all">';
						dt += '<a class="see-all-product"  href="'+response.products.url+'">';
						dt += '<span>'+MCS.AutoCompleteSearch.getViewAllLinkText+'</span>';
							if(MCS.AutoCompleteSearch.getCountSearchResults==1){
							dt +='(<span>'+response.products.size+'</span>)';		
							}										
						dt += '</a>';                
					dt +='</li>';
					
				}
				
			}else{
				
			dt +='<li>';
				dt += '<a class="see-all-product"  href="#">';
				dt += '<span>No Result</span>';					
				dt += '</a>';                
			dt +='</li>';
				
			}	
			
			
			
			dt += '</ul>';

			dt += '</div>';	

			if(response.category ||  response.pages){	
			
			dt += '<div class="mcs-autosearch-left">';
			if(response.category){
			var categoryResult=MCS.AutoCompleteSearch.categoryData(response);
				dt += categoryResult;
			}
			
			if(response.pages){
				
			var pagesResult=MCS.AutoCompleteSearch.pagesData(response);
				dt += pagesResult;
			}
			
			dt += '</div>';
			
			
			}
			
			dt += '</div>';	
				
			return 	dt;
	
	},
	
		categoryData : function (response){
			console.log(response.category);			
			
			var cdt='';			
			cdt += '<ul>';
			
			cdt += '<h4>Category</h4>';
			
				jQuery.each(response.category, function(index, element) {
					
					cdt += '<li class="search-cat">';
						
					if(element.catdata){
						
						if(element.isSet==1){	
						cdt += element.catdata;
						}else{							
						cdt +='<span>'+element.catdata+'</span>';	
						}
					
					}
															
					cdt += '</li>';
					
				});
			cdt += '</ul>';	
			
			return 	cdt;
		},
		
		pagesData : function (response){
			console.log(response.pages);			
			
			var pdt='';			
			pdt += '<ul>';
			pdt += '<h4>Pages</h4>';
				jQuery.each(response.pages, function(index, element) {
					
					pdt += '<li class="search-pages">';
					
					if(element.title){
							
						if(element.isSet==1){	
						pdt +='<a href="'+element.pageUrl+'"><span>'+element.title+'</span></a>'; 
						}else{
						pdt +='<span>'+element.title+'</span>';	
						}
				
					}					
					pdt += '</li>';
					
				});
			pdt += '</ul>';	
			
			return 	pdt;
		},
	
	}

	
define([
    "jquery",
    "jquery/ui"
], function($){
	
	 				

jQuery(document).ready(function(){

	
	 jQuery(document).click(function(e){

		if(jQuery(e.target).is('#custom_search_autocomplete *') || jQuery(e.target).is('#search_mini_form *')){			
			jQuery("#custom_search_autocomplete").show();			
		}else{
			jQuery("#custom_search_autocomplete").hide();
			//jQuery(".block-search").css("width", "250px");
		}
	});
	
	jQuery("input").focus(function(){
      //  jQuery(".block-search").css("width", "400px").fadeIn(1500);
    });
	
	
	
  });

});	