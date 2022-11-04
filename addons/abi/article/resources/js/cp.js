import ListingView from './components/Listing.vue'
import View from './components/View.vue'
import ArticleCreateForm from "./components/ArticleCreateForm";
import ArticlePublishForm from "./components/ArticlePublishForm";

Statamic.booting(() => {
    Statamic.$components.register('article-view', View);
    Statamic.$components.register('article-publish-form', ArticlePublishForm);
    Statamic.$components.register('article-create-form', ArticleCreateForm);
    Statamic.$components.register('article-list', ListingView);
});
// Statamic.$components.register('runway-listing-view', ListingView)

console.log('holalala')


