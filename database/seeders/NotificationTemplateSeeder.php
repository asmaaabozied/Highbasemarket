<?php

namespace Database\Seeders;

use App\Enum\NotificationChannel;
use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $templates = [
            [
                'key'     => 'order_placed',
                'channel' => NotificationChannel::WEB,
                'title'   => 'New Order Placed',
                'body'    => 'You’ve received a new order from a customer. Open your orders section to review the items and start processing.',
            ],
            [
                'key'     => 'order_cancelled_by_buyer',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Order Cancelled by Buyer',
                'body'    => 'The buyer has cancelled this order. You may contact them if follow-up or clarification is required.',
            ],
            [
                'key'     => 'order_cancelled',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Order Cancelled',
                'body'    => 'You’ve cancelled this order. The buyer has been notified, and the order is now closed.',
            ],
            [
                'key'     => 'timeline_updated',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Timeline Updated by {{company.name}}',
                'body'    => '{{company.name}} has updated their timeline. Visit their profile to stay up to date on activity and opportunities.',
            ],
            [
                'key'     => 'vendor_confirmed_step',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Vendor Confirmed a Step',
                'body'    => 'Your vendor confirmed {{step.name}} under Quote: {{quote.name}}. Review the tracker to follow up and continue the process.',
            ],
            [
                'key'     => 'step_rejected_by_vendor',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Step Rejected by Vendor',
                'body'    => 'The vendor rejected {{step.name}} in Quote: {{quote.name}}. Review the reason and update the tracker or follow up if needed.',
            ],
            [
                'key'     => 'proposal_received',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Proposal Received from Vendor',
                'body'    => 'A vendor responded to your posted RFQ with a proposal. Next step: Review and compare proposals in your dashboard.',
            ],
            [
                'key'     => 'rfq_posted',
                'channel' => NotificationChannel::WEB,
                'title'   => 'RFQ Posted to Marketplace',
                'body'    => 'Your RFQ is now live. Vendors can view and submit proposals. Next step: Track responses and compare proposals in your dashboard.',
            ],
            [
                'key'     => 'quote_submitted',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Quote Submitted by Vendor',
                'body'    => 'A vendor submitted a quote in response to your request. Review the details and proceed from your RFQ dashboard.',
            ],
            [
                'key'     => 'rfq_sent',
                'channel' => NotificationChannel::WEB,
                'title'   => 'RFQ Sent to Vendor',
                'body'    => 'Your RFQ has been sent. You’ll be notified when the vendor responds. Track progress from your RFQ dashboard.',
            ],
            [
                'key'     => 'proof_of_delivery_uploaded',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Proof of Delivery Uploaded',
                'body'    => 'You’ve added proof of delivery. This will be stored with your order details for reference.',
            ],
            [
                'key'     => 'order_status_updated',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Order Status Updated',
                'body'    => 'You’ve marked order {{order.number}} as delivered. Upload proof if needed and ensure your records are complete.',
            ],
            [
                'key'     => 'customer_added_from_link',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Customer added to your list.',
                'body'    => 'You accessed a customer’s link or scanned their QR/NFC sticker. They’ve been added to your customer list — share your catalog to receive orders.',
            ],
            [
                'key'     => 'new_customer_added',
                'channel' => NotificationChannel::WEB,
                'title'   => 'New Customer Added',
                'body'    => 'A customer accessed your link or scanned your QR/NFC business card. Start receiving orders from them directly.',
            ],
            [
                'key'     => 'team_role_assigned',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Team Role Assigned',
                'body'    => 'You’ve assigned a role to your team member. They now have access to the tools needed for their responsibilities.',
            ],
            [
                'key'     => 'new_team_member',
                'channel' => NotificationChannel::WEB,
                'title'   => 'New Team Member Added',
                'body'    => 'You’ve added a team member. Assign a role to let them manage products, customers, and orders more efficiently.',
            ],
            [
                'key'     => 'stock_updated',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Stock Updated',
                'body'    => 'You’ve updated stock information. Your customers will now see the latest availability and prices in your catalog.',
            ],
            [
                'key'     => 'stock_added',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Stock Added',
                'body'    => 'You’ve added stock to an existing product. It’s now active and visible in your catalog.',
            ],
            [
                'key'     => 'stock_added_to_product',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Stock Added to Your Product',
                'body'    => 'You’ve added stock successfully. Now add your customers to let them browse and place orders from your catalog.',
            ],
            [
                'key'     => 'product_updated',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Product Updated Successfully',
                'body'    => 'You’ve updated product information. All changes are now reflected in your catalog and branch listings.',
            ],
            [
                'key'     => 'new_product_added',
                'channel' => NotificationChannel::WEB,
                'title'   => 'New Product Added',
                'body'    => 'Your product has been added. Add stock so customers can start viewing and ordering.',
            ],
            [
                'key'     => 'brand_updated',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Brand Updated Successfully',
                'body'    => 'You’ve updated brand details. These changes are now reflected in your catalog and product listings.',
            ],
            [
                'key'     => 'new_brand_added',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Brand Added to Your Profile',
                'body'    => 'You’ve added a new brand. Assign it to your products to build a strong and trusted catalog.',
            ],
            [
                'key'     => 'brand_claim_received',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Brand Claim Received',
                'body'    => 'Your request to claim this brand has been submitted. We’ll notify you once it’s approved and added to your profile.',
            ],
            [
                'key'     => 'branch_updated',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Branch Information Updated',
                'body'    => 'Your branch details have been successfully updated. Keep your stock and customer list aligned for accurate order handling.',
            ],
            [
                'key'     => 'new_branch_added',
                'channel' => NotificationChannel::WEB,
                'title'   => 'New Branch Added',
                'body'    => 'You’ve added a new branch. Review its details and finish setup to ensure smooth operations.',
            ],
            [
                'key'     => 'branch_created',
                'channel' => NotificationChannel::WEB,
                'title'   => 'Your Branch Has Been Created',
                'body'    => 'You’ve added your branch. Add your products and your customers to activate your local operations.',
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                [
                    'key'     => $template['key'],
                    'channel' => $template['channel'],
                ],
                $template
            );
        }
    }
}
