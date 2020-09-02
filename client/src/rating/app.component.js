/* eslint-disable no-unreachable */
/* eslint-disable no-undef */
/* eslint-disable one-var */
/* eslint-disable no-unused-vars */
import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import AppForm from './app.form';
import { RateComponent } from 'silverstripe-react-ratingblock';

// import './rating.scss';
import 'silverstripe-react-ratingblock/dist/main.css';

@inject('appStore')
@observer
class AppComponent extends Component {
    constructor(props) {
        super(props);

        this.ratingStore = props.appStore;

        this.ratingForm = new AppForm();
        this.ratingForm.setStore(props.appStore);

        this.pageID = 1;
        this.pageName = 'elemental rating';
        this.enableRatingComments = true;

        if (window.bootData) {
            this.pageID = window.bootData.RatingPageID || 0;
            this.pageName = window.bootData.RatingPageName || 'unknown';
            this.successMessage = window.bootData.RatingFormSuccessMessage;
            this.enableRatingComments = window.bootData.EnableRatingComments;
            this.intro = window.bootData.RatingFormIntro;
            this.title = window.bootData.RatingFormTitle;
            this.stars = window.bootData.RatingStars ? (window.bootData.RatingStars.Max === 0 ? { 'Max': 5 } : window.bootData.RatingStars) : { 'Max': 5 };

            // set the page ID and carry through the form
            if (this.pageID) {
                this.ratingForm.set({ pageID: this.pageID });
            }

            // set the page Name (reference) and carry through the form
            if (this.pageName) {
                this.ratingForm.set({ pageName: this.pageName });
            }
        }

        this.setRatingValue = this.setRatingValue.bind(this);
        this.setCommentsValue = this.setCommentsValue.bind(this);
        this.setTagsValue = this.setTagsValue.bind(this);
        this.handleModalClose = this.handleModalClose.bind(this);
        this.onSubmit = this.onSubmit.bind(this);

        // check if this page has been previously rated by looking
        // for thje cookie with the pageName
        const rating = this.ratingStore.getRateValue() || 0,
            previouslyRated = rating > 0,
            tags = this.ratingStore.getTagsValue() || '';

        // set the local value from the cookie so we can show the
        // previously rated value
        if (rating) {
            this.setRatingValue(rating, false);
        }

        if (tags) {
            this.setTagsValue(tags);
        }

        this.state = {
            expanded: false,
            previouslyRated: previouslyRated
        };
    }

    /**
     * Set the rating value on the form
     *
     * @param {Int} value
     */
    setRatingValue(value) {
        this.ratingForm.set({ rating: value });
    }

    /**
     * Submit the form via the store
     * Submits the data to the graphql backend
     */
    onSubmit(e) {
        e.preventDefault();
        this.ratingForm.onSubmit(e);
    }

    setCommentsValue(value) {
        this.ratingForm.set({ comments: value });
    }

    setTagsValue(value) {
        this.ratingForm.set({ tags: value });
    }

    handleModalClose() {
        // this.setState({ modalOpen: !this.state.modalOpen });
        if (!this.ratingStore.submitted) {
            this.ratingForm.clear();
            this.setState({ modalOpen: false });
        } else {
            this.setState({ previouslyRated: true });
        }
    }

    render() {
        const
            form = {
                successMessage: this.successMessage || 'Thank you for your submission',
                intro: this.intro,
                title: this.title,
                submitted: this.ratingStore.submitted,
                comments: {
                    id: this.ratingForm.$('comments').id,
                    enabled: this.enableRatingComments,
                    value: this.ratingForm.$('comments').value,
                    name: this.ratingForm.$('comments').name,
                    placeholder: 'Add your comment'
                },
                tags: this.ratingForm.$('tags').value
            },
            page = {
                id: this.pageID,
                name: this.pageName
            },
            rating = this.ratingForm.$('rating').value || 0;

        return <div className='rating__wrapper'>
            <RateComponent
                name='Rating block'
                errors={this.ratingForm.errors()}
                value={rating}
                loading={this.ratingStore.loading}
                setRatingValue={this.setRatingValue}
                setCommentsValue={this.setCommentsValue}
                setTagsValue={this.setTagsValue}
                onSubmit={e => this.onSubmit(e)}
                stars={this.stars}
                form={form}
                page={page}
                previouslyRated={this.state.previouslyRated}
                enabled
            />
        </div>;
    }
}

export default AppComponent;
