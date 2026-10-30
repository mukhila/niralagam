<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogContentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@niralagam.com')->first();

        // ── Categories ────────────────────────────────────────────────────
        $cats = collect([
            ['name' => 'Technology',  'description' => 'Latest in tech, AI, and software',          'icon' => '💻'],
            ['name' => 'Design',      'description' => 'UI/UX, branding, and visual culture',        'icon' => '🎨'],
            ['name' => 'Lifestyle',   'description' => 'Wellness, productivity, and everyday living', 'icon' => '🌿'],
            ['name' => 'Culture',     'description' => 'Art, literature, music, and tradition',       'icon' => '🏛️'],
            ['name' => 'Travel',      'description' => 'Destinations, tips, and travel stories',      'icon' => '✈️'],
            ['name' => 'Food',        'description' => 'Recipes, restaurants, and food culture',      'icon' => '🍜'],
        ])->mapWithKeys(function ($c) {
            $cat = Category::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                ['name' => $c['name'], 'description' => $c['description'], 'icon' => $c['icon']]
            );
            $cat->update(['icon' => $c['icon']]);
            return [$c['name'] => $cat];
        });

        // ── Tags ──────────────────────────────────────────────────────────
        $tags = collect(['AI', 'React', 'Laravel', 'Minimalism', 'Tamil Nadu',
            'Chennai', 'Sustainability', 'Remote Work', 'Photography', 'Wellness'])
            ->mapWithKeys(function ($t) {
                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($t)],
                    ['name' => $t]
                );
                return [$t => $tag];
            });

        // ── Posts ─────────────────────────────────────────────────────────
        $posts = [
            [
                'title'          => 'The Future of AI in Everyday Life',
                'excerpt'        => 'Artificial intelligence is no longer confined to science fiction — it\'s reshaping how we work, communicate, and experience the world around us.',
                'content'        => '<p>Artificial intelligence is quietly embedding itself into every layer of our daily existence. From the recommendation algorithms that curate your morning news feed to the predictive text finishing your sentences, AI has become an invisible but indispensable partner in modern life.</p><p>The workplace is undergoing its most significant transformation since the industrial revolution. Repetitive, rule-based tasks are being automated at breathtaking speed, freeing human workers to focus on creativity, empathy, and complex problem-solving — the things machines still struggle to replicate. Companies that embrace this shift thoughtfully are finding productivity gains that were unimaginable just five years ago.</p><p>In healthcare, AI diagnostic tools are catching early signs of disease with accuracy that rivals specialist physicians. In education, adaptive learning platforms adjust in real time to each student\'s pace and learning style. Even the arts are being touched — generative models create music, images, and text that blur the line between human and machine creativity.</p><p>Yet the transition raises profound questions about privacy, employment, and the nature of intelligence itself. The challenge for our generation is not whether to adopt AI, but how to do so wisely — ensuring that its benefits are distributed equitably and its risks are managed responsibly.</p>',
                'category'       => 'Technology',
                'featured_image' => 'https://images.unsplash.com/photo-1677442135703-1787eea5ce01?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['AI'],
            ],
            [
                'title'          => 'Designing for Accessibility: A Practical Guide',
                'excerpt'        => 'Great design is invisible. When done right, it removes friction for everyone — including the one in four people worldwide living with a disability.',
                'content'        => '<p>Accessibility is not a feature you bolt on at the end of a project. It is a mindset that, when adopted early, produces designs that are cleaner, more intuitive, and better for everyone. The principles that make an interface navigable for a screen reader user often make it easier for a tired parent holding a baby with one hand, or a commuter glancing at their phone in bright sunlight.</p><p>Start with contrast. The Web Content Accessibility Guidelines (WCAG) require a minimum contrast ratio of 4.5:1 for normal text. This is not just a compliance checkbox — it makes your text legible in a wide range of lighting conditions. Tools like Stark and the built-in accessibility inspector in browser DevTools make this check effortless.</p><p>Focus states deserve your attention. When someone navigates a page with a keyboard, the focused element is their only orientation cue. A hidden or poorly styled focus ring leaves keyboard users completely lost. Design focus states that are unmistakably visible and fit your visual language — a thick, offset ring in your primary color works beautifully in almost every context.</p><p>Motion can be a barrier. Many people experience nausea or disorientation from animations. The <code>prefers-reduced-motion</code> media query lets you respect the system-level setting users have configured, offering a gentler experience without abandoning animation entirely for those who enjoy it.</p>',
                'category'       => 'Design',
                'featured_image' => 'https://images.unsplash.com/photo-1561070791-2526d30994b5?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['Minimalism'],
            ],
            [
                'title'          => 'Hidden Gems of Tamil Nadu You Must Visit',
                'excerpt'        => 'Beyond the temples and hill stations that draw millions of tourists, Tamil Nadu holds quieter wonders waiting to be discovered by the curious traveller.',
                'content'        => '<p>Tamil Nadu is one of India\'s most storied states, layered with ancient Dravidian architecture, a coastline that stretches for nearly a thousand kilometres, and a culinary tradition that could fill a lifetime of exploration. Yet most visitors follow the same well-worn circuit: Chennai, Madurai, Ooty, Rameswaram. Step off that path and the rewards are extraordinary.</p><p>Tranquebar, tucked on the Coromandel Coast, is a former Danish colonial settlement that time seems to have largely forgotten. A fort built in 1620, a tidy grid of streets with peeling pastel facades, and a beach utterly free of development — it is unlike anywhere else in India. The town moves at a pace that invites you to slow down too.</p><p>Yelagiri, a small hill station in the Vellore district, has none of the crowds that descend on Ooty or Kodaikanal. A single winding road leads to its plateau, which sits at about 1100 metres. You will find a small lake, orchid nurseries, and trails through forests that feel genuinely wild. The sunsets here, unimpeded by tourist infrastructure, are luminous.</p><p>For those drawn to art and craft, Swamimalai near Kumbakonam is the living centre of Chola bronze casting. Families have practised the lost-wax technique here for generations, and many workshops welcome visitors to watch the process — from the sculpting of the wax model to the final polishing of the bronze.</p>',
                'category'       => 'Travel',
                'featured_image' => 'https://images.unsplash.com/photo-1582510003544-4d00b7f74220?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['Tamil Nadu'],
            ],
            [
                'title'          => 'Why React Still Dominates Web Development in 2025',
                'excerpt'        => 'Despite a wave of compelling alternatives, React\'s ecosystem, community, and compositional model keep it firmly at the centre of modern frontend development.',
                'content'        => '<p>Every year, a new framework arrives promising to finally dethrone React. And every year, React\'s share of the frontend landscape holds remarkably steady. Understanding why requires looking beyond benchmark comparisons to the deeper structural advantages that React has built over a decade.</p><p>The component model is the foundation. React popularised the idea of building UIs from composable, reusable pieces, and that mental model has proven so durable that virtually every framework that emerged after it — Vue, Svelte, Solid, Angular\'s renaissance — adopted some version of it. React didn\'t just win the war; it defined the battlefield.</p><p>The ecosystem is the moat. Thousands of libraries are built with React in mind. State management solutions, testing utilities, animation libraries, data-fetching abstractions — the React community has solved almost every problem you will encounter, and the solutions are mature, well-documented, and maintained. Switching frameworks means leaving that ecosystem behind, which is a cost most teams are unwilling to pay.</p><p>React Server Components represent the most significant architectural evolution since hooks. By moving data fetching and rendering to the server while retaining client-side interactivity where it matters, they offer a model that is both performant and ergonomic. Frameworks like Next.js have made this accessible to every developer, and the pattern is beginning to reshape how we think about the client-server boundary.</p>',
                'category'       => 'Technology',
                'featured_image' => 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['React'],
            ],
            [
                'title'          => 'Minimalist Living: The Art of Owning Less',
                'excerpt'        => 'Stripping your life back to its essentials is not about deprivation. It\'s about clarity — and the unexpected freedom that arrives when you stop managing stuff.',
                'content'        => '<p>The appeal of minimalism is easy to understand in the abstract but surprisingly hard to act on. We live in an age of frictionless consumption — a single tap delivers almost anything to your door within hours. Every object you own, though, demands something in return: space, attention, maintenance, mental bandwidth. Minimalism is the practice of asking whether that trade is worth it.</p><p>Start with a single drawer. Not your wardrobe, not your garage — just one drawer. Pull everything out, hold each item, and ask: when did I last use this? Do I own something else that does the same thing? Would I buy this today if I didn\'t already own it? The answers are often illuminating. Most drawers contain items you have not thought about in years.</p><p>The emotional dimension is the part minimalism evangelists sometimes underplay. Objects carry stories. The gift from someone who is gone. The hobby you always meant to take up. Letting these things go requires confronting what they represent, which is why decluttering can feel unexpectedly heavy. Give yourself time, and be gentle with the process.</p><p>What you gain in return is difficult to quantify but unmistakable once you experience it. Spaces become calmer. Mornings become easier — there are fewer decisions to make, fewer things to move to find what you need. You spend less money, which buys you time. And you discover, usually to your surprise, that you do not miss most of what you gave away.</p>',
                'category'       => 'Lifestyle',
                'featured_image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['Minimalism', 'Wellness'],
            ],
            [
                'title'          => 'The Renaissance of Tamil Literature',
                'excerpt'        => 'A new generation of Tamil writers is reclaiming the language\'s literary tradition, blending classical forms with contemporary themes that resonate across the diaspora.',
                'content'        => '<p>Tamil is one of the world\'s oldest living languages, with a literary tradition stretching back more than two millennia. The Sangam poetry of the classical period — spare, imagistic, deeply attentive to both the natural world and the interior life — remains remarkable by any standard. Yet for much of the twentieth century, Tamil literature was overshadowed in international circles by writing in Hindi and English. That is changing.</p><p>A cohort of writers working in Tamil today are producing work of striking ambition and range. Perumal Murugan, whose novel Madhorubhagan (One Part Woman) provoked a censorship controversy that brought international attention to Tamil fiction, has since become one of the most celebrated Indian authors of his generation. His work is rooted in the landscapes and communities of the Kongu region, but its preoccupations — caste, desire, belonging — resonate far beyond their setting.</p><p>Equally significant is the surge of writing by Tamil women. Authors like Kutti Revathi and Salma have used poetry and fiction to explore experiences that were rarely addressed in the literary mainstream, navigating questions of female desire, domestic constraint, and identity with a directness that has found large and devoted readerships.</p><p>The diaspora is a crucial part of this story. Tamil communities in Sri Lanka, Singapore, Malaysia, Canada, the United Kingdom, and elsewhere have maintained their language and produced their own distinct literary voices. The internet has allowed these traditions to cross-pollinate as never before, producing a global Tamil literary conversation that is richer and more varied than at any point in recent history.</p>',
                'category'       => 'Culture',
                'featured_image' => 'https://images.unsplash.com/photo-1524578271613-d550eacf6090?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['Tamil Nadu'],
            ],
            [
                'title'          => 'Farm to Table: Chennai\'s New Food Movement',
                'excerpt'        => 'A quiet revolution is underway in Chennai\'s restaurant scene, as chefs forge direct relationships with farmers and rediscover ingredients that supermarkets forgot.',
                'content'        => '<p>For most of Chennai\'s history, the city\'s food culture has been dominated by an extraordinary tiffin tradition and a temple cuisine that has no real equivalent anywhere else in India. What is newer, and genuinely exciting, is the way a generation of chefs and restaurateurs is reorienting around locality and seasonality — principles that the city\'s grandmothers understood intuitively but that got lost somewhere between urbanisation and the arrival of multinational supply chains.</p><p>At the forefront of this movement is a network of small farms in the districts surrounding the city. Some grow native rice varieties — the deep-red mappillai samba, the aromatic kichili samba — that had largely disappeared from the market in favour of high-yield hybrids. Others are reviving forgotten vegetables: different varieties of ash gourd, multiple types of amaranth, long beans in a range of colours that you would never find in a supermarket.</p><p>Chefs who work with these producers speak in almost identical terms about what they discovered. The ingredient quality is so dramatically different, they say, that it changes the cooking. You do not need to do much. You just need to not ruin it. That simplicity is reshaping menus — fewer components, less complexity, more attention to the produce itself.</p><p>The movement is also beginning to influence home cooking. Farmers\' markets, once a niche urban curiosity, are expanding and attracting increasingly mainstream customers. Social media has given small producers a direct channel to consumers that bypasses the wholesale system entirely. And a generation of home cooks, raised on international food media, is discovering that the most interesting ingredients were often growing close to home all along.</p>',
                'category'       => 'Food',
                'featured_image' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['Chennai', 'Sustainability'],
            ],
            [
                'title'          => 'The Art of Slow Travel in South India',
                'excerpt'        => 'In an age of compressed itineraries and bucket lists, slow travel offers something different: the chance to actually know a place, rather than simply pass through it.',
                'content'        => '<p>There is a particular kind of exhaustion that comes from moving too fast through beautiful places. You photograph the temple, consult the guidebook, tick the box, and move on — carrying away a collection of images and very little understanding. Slow travel is the antidote: the deliberate choice to spend more time in fewer places, to arrive somewhere without an exit strategy.</p><p>South India rewards slowness in a way that few regions of the world can match. A week in a single village in the Nilgiris will teach you more about the relationship between the land, the people, and the food than a month-long tour of highlights could. You start to notice things: the way the mist moves through the valley in the morning, which flowers open at which hour, the rhythm of the weekly market, the faces that become familiar.</p><p>The practical requirements of slow travel are simpler than they seem. You need accommodation that allows extended stays — homestays, small guesthouses, or short-term rentals are usually better suited than hotels. You need a willingness to be without a plan, to follow curiosity rather than itinerary. And you need to accept that some days will feel uneventful, which is not the same as being wasted.</p><p>What slow travel returns to you is a different kind of memory. Not a slideshow of landmarks but something more textured — the smell of the kitchen in the morning, a conversation with a stranger that turned unexpectedly profound, the afternoon you spent watching a craftsman work and began to understand something about patience and skill. These are the things you carry home, and they last.</p>',
                'category'       => 'Travel',
                'featured_image' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=900&q=80&auto=format&fit=crop',
                'tags'           => ['Tamil Nadu', 'Wellness'],
            ],
        ];

        foreach ($posts as $data) {
            $post = Post::create([
                'user_id'        => $admin->id,
                'category_id'    => $cats[$data['category']]->id,
                'title'          => $data['title'],
                'slug'           => Str::slug($data['title']),
                'excerpt'        => $data['excerpt'],
                'content'        => $data['content'],
                'featured_image' => $data['featured_image'],
                'status'         => 'published',
                'published_at'   => now()->subDays(rand(1, 60)),
            ]);

            $postTags = collect($data['tags'])->map(fn ($t) => $tags[$t]->id)->all();
            $post->tags()->sync($postTags);
        }
    }
}
