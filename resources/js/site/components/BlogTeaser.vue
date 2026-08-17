<script setup>
import { ref } from 'vue'
import { t } from '../i18n.js'

/**
 * The three newest articles, handed over from the server on `#app[data-latest]`.
 *
 * This band exists for one reason: a blog the landing page never links to is a
 * blog Google treats as an orphan, and every article written for it is wasted.
 * If there are no posts yet, the whole section disappears.
 */
const posts = ref([])

try {
  const raw = document.querySelector('#app')?.dataset.latest
  if (raw) posts.value = JSON.parse(raw)
} catch {
  posts.value = []
}
</script>

<template>
  <section
    v-if="posts.length"
    aria-labelledby="blog-teaser"
    class="border-t border-navy-2 py-[clamp(3.5rem,8vh,5.5rem)]"
  >
    <div class="container-zl grid gap-x-14 gap-y-10 lg:grid-cols-12">
      <!-- pinned beside the list, which is always the taller column -->
      <div class="lg:col-span-4 lg:sticky lg:top-28 lg:self-start">
        <h2 id="blog-teaser" class="text-h3 text-cream">{{ t.blogTeaser.heading }}</h2>
        <p v-reveal="60" class="prose-zl mt-4 max-w-[38ch]">
          {{ t.blogTeaser.lead }}
        </p>
        <a
          v-reveal="120"
          href="/blog"
          class="mt-6 inline-block rounded-full border border-navy-3 px-6 py-3 text-[0.92rem] font-medium text-cream transition-colors duration-300 hover:border-flame/60 hover:bg-navy-2"
        >
          {{ t.blogTeaser.allArticles }}
        </a>
      </div>

      <ol class="min-w-0 lg:col-span-8">
        <li v-for="(post, i) in posts" :key="post.url" class="border-t border-navy-2 first:border-t-0">
          <a
            v-reveal="i * 70"
            :href="post.url"
            class="group grid gap-4 py-6 sm:grid-cols-12 sm:items-center"
          >
            <div class="sm:col-span-3">
              <div class="aspect-4/3 overflow-clip rounded-xl border border-navy-2 bg-navy-2">
                <img
                  v-if="post.image"
                  :src="post.image"
                  :alt="post.title"
                  loading="lazy"
                  width="240"
                  height="180"
                  class="size-full object-cover transition-transform duration-700 ease-out-quart group-hover:scale-[1.05]"
                />
              </div>
            </div>

            <div class="sm:col-span-9">
              <p class="flex flex-wrap items-center gap-x-2.5 text-[0.75rem] text-cream-3">
                <span>{{ post.date }}</span>
                <span aria-hidden="true">·</span>
                <span><span class="num">{{ post.minutes }}</span> {{ t.blogTeaser.minutesUnit }}</span>
              </p>
              <h3
                class="mt-1.5 text-[1.1rem] leading-[1.55] font-semibold text-cream transition-colors duration-300 group-hover:text-flame-ink-hi sm:text-[1.25rem]"
              >
                {{ post.title }}
              </h3>
              <p v-if="post.excerpt" class="mt-1.5 max-w-[58ch] text-[0.92rem] leading-[1.85] text-cream-2">
                {{ post.excerpt }}
              </p>
            </div>
          </a>
        </li>
      </ol>
    </div>
  </section>
</template>
