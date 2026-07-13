#!/usr/bin/env python3
import os
import sys
import json
import time
from datetime import datetime
import instaloader

# Configuration overrides (falls back to defaults if not set in environment or .env)
TARGET_PROFILE = os.environ.get("INSTAGRAM_TARGET_PROFILE", "himatekkom_its")
OUTPUT_PATH = os.environ.get("INSTAGRAM_OUTPUT_PATH", "storage/app/instagram_lomba.json")

# Instaloader credentials (highly recommended to set these to avoid anonymous rate limits)
IG_USERNAME = os.environ.get("INSTAGRAM_USERNAME")
IG_PASSWORD = os.environ.get("INSTAGRAM_PASSWORD")

def main():
    print(f"[{datetime.now()}] Starting Instagram Scraper...")
    
    # Initialize Instaloader and disable heavy asset downloads to keep it fast
    L = instaloader.Instaloader(
        download_pictures=False,
        download_videos=False,
        download_video_thumbnails=False,
        download_geotags=False,
        download_comments=False,
        save_metadata=False,
        compress_json=False
    )
    
    # Optional login to bypass strict rate limiting and anonymous content walls
    if IG_USERNAME and IG_PASSWORD:
        try:
            print(f"Logging in as {IG_USERNAME}...")
            L.login(IG_USERNAME, IG_PASSWORD)
            print("Login successful!")
        except Exception as e:
            print(f"Login failed: {e}. Proceeding anonymously.", file=sys.stderr)
            
    posts_data = []
    
    try:
        print(f"Loading posts for Instagram profile: @{TARGET_PROFILE}...")
        profile = instaloader.Profile.from_username(L.context, TARGET_PROFILE)
        
        count = 0
        matched_count = 0
        
        # Pull only the most recent posts (e.g. 30) to remain polite and fast
        for post in profile.get_posts():
            if count >= 30:
                break
                
            caption = post.caption or ""
            caption_lower = caption.lower()
            
            # Keywords used to identify competition (lomba) announcements
            keywords = ["lomba", "kompetisi", "competition", "contest", "championship", "tournament", "acara"]
            is_lomba_post = any(kw in caption_lower for kw in keywords)
            
            if is_lomba_post:
                post_info = {
                    "id": post.mediaid,
                    "shortcode": post.shortcode,
                    "url": f"https://www.instagram.com/p/{post.shortcode}/",
                    "caption": caption,
                    "date": post.date_utc.isoformat(),
                    "likes": post.likes,
                    "comments_count": post.comments,
                    "display_url": post.url,  # Direct image URL (note: Instagram CDN URLs expire after a few days)
                    "typename": post.typename
                }
                posts_data.append(post_info)
                matched_count += 1
                print(f"[{matched_count}] Match found: https://www.instagram.com/p/{post.shortcode}/ (Date: {post.date_utc})")
                
            count += 1
            # Polite delay between profile iterations to avoid triggers
            time.sleep(2)
            
    except Exception as e:
        print(f"Error occurred during scraping execution: {e}", file=sys.stderr)
        
    # Ensure directory folder exists
    os.makedirs(os.path.dirname(OUTPUT_PATH), exist_ok=True)
    
    # Save the output structured data
    try:
        with open(OUTPUT_PATH, 'w', encoding='utf-8') as f:
            json.dump(posts_data, f, indent=4, ensure_ascii=False)
        print(f"Successfully saved {len(posts_data)} competition posts to: {OUTPUT_PATH}")
    except Exception as e:
        print(f"Error saving scraper output: {e}", file=sys.stderr)

if __name__ == "__main__":
    main()
