/* eslint-disable no-unreachable */
/* eslint-disable no-undef */
/* eslint-disable one-var */
/* eslint-disable no-unused-vars */
import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import AppForm from './app.form';
import { RateComponent } from 'silverstripe-react-ratingblock';

import 'silverstripe-react-ratingblock/dist/main.css';

@inject('appStore')
@observer
class AppComponent extends Component {
    constructor(props) {
        super(props);

        this.appStore = props.appStore;

        this.ratingForm = new AppForm();
        this.ratingForm.setStore(props.appStore);

        this.pageID = 1;
        this.pageName = 'elemental rating';
        this.enableRatingComments = true;

        if (window.bootData) {
            this.pageID = window.bootData.RatingPageID;
            this.pageName = window.bootData.RatingPageName;
            this.successMessage = window.bootData.RatingFormSuccessMessage;
            this.enableRatingComments = window.bootData.EnableRatingComments;
            this.intro = window.bootData.RatingFormIntro;
            this.title = window.bootData.RatingFormTitle;
            this.stars = window.bootData.RatingStars;

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
        this.onSubmit = this.onSubmit.bind(this);

        // check if this page has been previously rated by looking
        // for thje cookie with the pageName
        const rating = this.appStore.rateCookie.value || 0,
            previouslyRated = rating > 0,
            tags = this.appStore.tagsCookie.value || '';

        // set the local value from the cookie so we can show the
        // previously rated value
        if (rating) {
            this.setRatingValue(rating);
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

    render() {
        const
            form = {
                successMessage: this.successMessage || 'Thank you for your submission',
                intro: this.intro,
                title: this.title,
                submitted: this.appStore.submitted,
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
            rating = this.ratingForm.$('rating').value;
        return (
            <RateComponent
                name='Rating block'
                errors={this.ratingForm.errors()}
                value={rating}
                loading={this.appStore.loading}
                setRatingValue={this.setRatingValue}
                setCommentsValue={this.setCommentsValue}
                setTagsValue={this.setTagsValue}
                onSubmit={e => this.onSubmit(e)}
                stars={this.stars}
                form={form}
                page={page}
                enabled
            />
        );
    }
}

export default AppComponent;
