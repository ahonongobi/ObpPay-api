let selectedKycId = null;

window.openKycModal = function (id) {
    selectedKycId = id;

    document.getElementById("modalContent").innerHTML = `
        <div class="text-center">
            <div class="spinner-border text-primary"></div>
        </div>
    `;

    const modal = new bootstrap.Modal(document.getElementById("kycModal"));
    modal.show();

    fetch(`/admin/kyc/${id}`)
        .then((res) => res.json())
        .then((data) => {
            let docsHtml = data.docs
                .map(
                    (doc) => `
                <div class="col-md-4 text-center">
                    <h6 class="fw-bold text-primary">${doc.type}</h6>
                    <img src="/storage/${doc.path}" 
                         class="img-fluid rounded shadow mb-3">
                </div>
            `
                )
                .join("");

            document.getElementById("modalContent").innerHTML = `
                <h4 class="text-primary">${data.user.name}</h4>
                <p><strong>Téléphone :</strong> ${data.user.phone}</p>
                <p><strong>Status :</strong> ${data.status}</p>

                <hr>

                <div class="row">${docsHtml}</div>
            `;
        });
};

document.getElementById("approveBtn").onclick = () => {
    fetch(`/admin/kyc/${selectedKycId}/approve`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.head.querySelector(
                'meta[name="csrf-token"]'
            ).content,
        },
    }).then(() => location.reload());
};

document.getElementById("rejectBtn").onclick = () => {
    fetch(`/admin/kyc/${selectedKycId}/reject`, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.head.querySelector(
                'meta[name="csrf-token"]'
            ).content,
        },
    }).then(() => location.reload());
};
