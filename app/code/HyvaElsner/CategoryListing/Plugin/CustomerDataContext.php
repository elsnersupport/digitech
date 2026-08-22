<?php
declare(strict_types=1);

namespace HyvaElsner\CategoryListing\Plugin;

use Magento\Framework\App\FrontController;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Http\Context;

class CustomerDataContext
{
    /**
     * @var Session
     */
    private Session $customerSession;
    /**
     * @var Context
     */
    private Context $httpContext;
    /**
     * Constructor
     *
     * @param Session $customerSession
     * @param Context $httpContext
     */
    public function __construct(
        Session $customerSession,
        Context $httpContext
    ) {
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }
    /**
     * @param FrontController $subject
     * @param RequestInterface $request
     * @return RequestInterface[]
     */
    public function beforeDispatch(FrontController $subject, RequestInterface $request)
    {
        $this->httpContext->setValue('customer_id', $this->customerSession->getCustomerId(), false);
        $fullName = $this->customerSession->getFirstName() . " " . $this->customerSession->getLastName();
        $this->httpContext->setValue('customer_fullname', $fullName, false);
        return [$request];
    }
}