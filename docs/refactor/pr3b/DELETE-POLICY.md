# PR3B Delete Policy

- Referenced Product: hard delete blocked; use inactive status.
- Referenced Post: hard delete blocked when used by Home Content or Menu Items.
- Category with children or posts: delete blocked.
- Referenced Media: delete blocked with reference types/counts.
- Unreferenced Media: database row is deleted transactionally, then the physical file is deleted after commit.
- ProductImage: only product-owned files may be deleted; a Media asset is never deleted by removing a ProductImage.
- Menu tree deletion: explicit confirmation and server-owned menu item ownership; no delete-all/recreate sync.

No force-delete path is exposed in PR3B.
