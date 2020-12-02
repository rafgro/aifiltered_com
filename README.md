# AIfiltered.com website

Simple aggregator of various ML-related sources in one place:
- discussions from subreddits: r/machinelearning and (heavily filtered) r/futurology
- Andrej Karpathy's, Papers with code's, and DeepAI's stats on papers from arxiv
- articles from google news suggested in artificial intelligence section (also heavily filtered)
- industry blogs (DeepMind, Microsoft, Google, OpenAI) and good magazines (Synced Review, The Conversation, AI Weirdness)
- subjective choice of quality podcasts (TWiML, Let's Talk AI, Yannic Kilcher, Two Minute Papers, Lex Fridman) and education blogs (ML Mastery, KDnuggets, Jay Alammar, The Gradient, Distill.pub)

How it works: each item is scored by weights given to specific sources, popularity metrics, and rough quality evaluations; then items are sorted by the score, cut for chosen timeframes, and sorted again, usually by date (in one case - last month page - original top scoring is left).
