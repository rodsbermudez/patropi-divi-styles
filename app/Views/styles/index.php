<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="row">
    <div class="col-12">
        <h1 class="mb-4">Estilos Disponíveis</h1>
        
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">Lista de Estilos</div>
            <div class="card-body">
                <?php if (empty($styles)): ?>
                    <p class="text-muted">Nenhum estilo disponível.</p>
                <?php else: ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Label</th>
                                <th>Tipo</th>
                                <th>Criado em</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($styles as $style): ?>
                                <tr>
                                    <td><?= $style['id'] ?></td>
                                    <td><?= $style['label'] ?></td>
                                    <td><span class="badge bg-secondary"><?= $style['tipo'] ?></span></td>
                                    <td><?= $style['created_at'] ? date('d/m/Y H:i', strtotime($style['created_at'])) : '-' ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewModal<?= $style['id'] ?>">Ver</button>
                                    </td>
                                </tr>
                                <div class="modal fade" id="viewModal<?= $style['id'] ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title"><?= $style['label'] ?> - <?= $style['tipo'] ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h6>Element Info:</h6>
                                                <pre><?= $style['element_info'] ?? 'N/A' ?></pre>
                                                <h6>Styles:</h6>
                                                <pre><?= $style['styles'] ?? 'N/A' ?></pre>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>