# Sculpin RelatedContentBundle

This Bundle is written to work with [Sculpin](http://sculpin.io).

## Installation

#### Composer

    $ composer require wjzijderveld/sculpin-related-content-bundle ~1.0

## Usage

At this moment, the usage is pretty simple, but can be expanded later on. It works
with Sculpin Taxonomy. 


First you need to set some tags on you content:

    ---
    title: Foo document
    tags: [foo, bar]
    ---


On every document where you want to show related content, you define the tags
to relate this content to:


    ---
    title: Foobar Document
    tags: [foo, bar, foobar]
    related_content:
        post_tags: [foo]
    ---


When you have done this, you can now use the `related_content` variable in your
templates (in this example Twig):


    {% if related_content|length %}
        <div class="related">
            {% for item in related_content %}
                <a href="{{ item.url }}">{{ item.title }}</a>
            {% endfor %}
        </div>
    {% endif %}

