import ListingView from './components/Listing.vue'

Statamic.booting(() => {
    Statamic.$components.register('article-list', ListingView);
});
// Statamic.$components.register('runway-listing-view', ListingView)

console.log('holalala')


